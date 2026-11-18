<?php

namespace App\Services;

use App\Models\ClienteOS;
use App\Models\Configuracao;
use App\Models\WhatsappOptOut;

class WhatsappDispatcher
{
    private const LOCK_FILE = '/tmp/nps-whatsapp-worker.lock';
    private const MAX_CONSECUTIVE_FAILURES = 3;

    private ClienteOS $clientes;
    private Configuracao $configuracao;
    private WhatsappOptOut $optOut;
    private array $settings;
    private \Closure $sleep;
    private \Closure $log;

    public function __construct(?array $settings = null, ?callable $sleep = null, ?callable $log = null)
    {
        $this->clientes = new ClienteOS();
        $this->configuracao = new Configuracao();
        $this->optOut = new WhatsappOptOut();
        $this->settings = $settings ?? self::settingsFromEnv();
        $this->sleep = \Closure::fromCallable($sleep ?? 'sleep');
        $this->log = \Closure::fromCallable($log ?? static function (string $message): void {
            @file_put_contents('/tmp/nps-whatsapp-worker.log', date('c') . ' ' . $message . "\n", FILE_APPEND);
        });
    }

    public static function settingsFromEnv(): array
    {
        $int = static function (string $name, int $default): int {
            $value = getenv($name);

            return $value !== false && ctype_digit($value) ? (int) $value : $default;
        };

        $delayMin = $int('WHATSAPP_DELAY_MIN', 30);
        $batchMin = max(1, $int('WHATSAPP_BATCH_MIN', 8));
        $pauseMin = $int('WHATSAPP_BATCH_PAUSE_MIN', 300);

        return [
            'delay_min' => $delayMin,
            'delay_max' => max($delayMin, $int('WHATSAPP_DELAY_MAX', 90)),
            'batch_min' => $batchMin,
            'batch_max' => max($batchMin, $int('WHATSAPP_BATCH_MAX', 15)),
            'pause_min' => $pauseMin,
            'pause_max' => max($pauseMin, $int('WHATSAPP_BATCH_PAUSE_MAX', 600)),
            'daily_limit' => $int('WHATSAPP_DAILY_LIMIT', 80),
            'hour_start' => $int('WHATSAPP_HOUR_START', 8),
            'hour_end' => $int('WHATSAPP_HOUR_END', 20),
        ];
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    // Retorna o motivo que impede novos envios agora, ou null se estiver liberado.
    public function blockReason(?\DateTimeInterface $agora = null): ?string
    {
        $hora = (int) ($agora ?? new \DateTimeImmutable())->format('G');
        if ($hora < $this->settings['hour_start'] || $hora >= $this->settings['hour_end']) {
            return sprintf(
                'fora do horário de envio (%02dh às %02dh)',
                $this->settings['hour_start'],
                $this->settings['hour_end']
            );
        }

        if ($this->clientes->countWhatsappSentToday() >= $this->settings['daily_limit']) {
            return 'limite diário de ' . $this->settings['daily_limit'] . ' mensagens atingido';
        }

        return null;
    }

    public function run(string $publicBaseUrl): void
    {
        $lock = fopen(self::LOCK_FILE, 'c');
        if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
            return;
        }

        try {
            $this->process(rtrim($publicBaseUrl, '/'));
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function process(string $publicBaseUrl): void
    {
        $ids = array_column($this->clientes->getWhatsappPending(), 'id');
        $enviadosNoLote = 0;
        $tamanhoLote = random_int($this->settings['batch_min'], $this->settings['batch_max']);
        $falhasSeguidas = 0;
        $total = count($ids);

        ($this->log)("Início do envio gradual: $total mensagens pendentes.");

        foreach ($ids as $posicao => $id) {
            $config = $this->configuracao->get();
            if (empty($config['evolution_enabled'])) {
                ($this->log)('WhatsApp desativado; envio interrompido.');
                return;
            }

            if (($motivo = $this->blockReason()) !== null) {
                ($this->log)('Envio interrompido: ' . $motivo . '.');
                return;
            }

            $cliente = $this->clientes->findById((int) $id);
            if (
                $cliente === null
                || empty($cliente['telefone_whatsapp'])
                || !in_array($cliente['status_whatsapp'], ['nao_enviado', 'retornado_erro'], true)
            ) {
                continue;
            }

            try {
                if ($this->optOut->isOptedOut((string) $cliente['telefone_whatsapp'])) {
                    $this->clientes->updateStatusWhatsapp((int) $id, 'nao_disponivel');
                    continue;
                }

                $evolution = new EvolutionApi(
                    (string) ($config['evolution_url'] ?? ''),
                    (string) ($config['evolution_api_key'] ?? ''),
                    (string) ($config['evolution_instance'] ?? '')
                );
                $mensagem = WhatsappMessageBuilder::build(
                    $cliente,
                    $this->companyName($config),
                    $publicBaseUrl . '/pesquisa?token=' . $cliente['token_unico']
                );
                $evolution->sendText((string) $cliente['telefone_whatsapp'], $mensagem, random_int(1500, 4500));
                $this->clientes->updateStatusWhatsapp((int) $id, 'enviado');
                $enviadosNoLote++;
                $falhasSeguidas = 0;
            } catch (\Throwable $e) {
                ($this->log)('Erro no registro ' . $id . ': ' . $e->getMessage());
                $this->clientes->updateStatusWhatsapp((int) $id, 'retornado_erro');
                if (++$falhasSeguidas >= self::MAX_CONSECUTIVE_FAILURES) {
                    ($this->log)('Envio interrompido após falhas consecutivas.');
                    return;
                }
            }

            if ($posicao === $total - 1) {
                break;
            }

            if ($enviadosNoLote >= $tamanhoLote) {
                $pausa = random_int($this->settings['pause_min'], $this->settings['pause_max']);
                $enviadosNoLote = 0;
                $tamanhoLote = random_int($this->settings['batch_min'], $this->settings['batch_max']);
            } else {
                $pausa = random_int($this->settings['delay_min'], $this->settings['delay_max']);
            }
            ($this->sleep)($pausa);
        }

        ($this->log)('Envio gradual concluído.');
    }

    private function companyName(array $config): string
    {
        return \App\Core\View::appName();
    }
}
