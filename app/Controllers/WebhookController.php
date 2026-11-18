<?php

namespace App\Controllers;

use App\Models\ClienteOS;
use App\Models\Configuracao;
use App\Models\WhatsappOptOut;
use App\Services\BrazilianPhoneNumber;
use App\Services\EvolutionApi;

class WebhookController
{
    private const OPT_OUT_WORDS = [
        'sair', 'parar', 'pare', 'stop', 'cancelar', 'descadastrar', 'remover',
        'nao quero receber', 'nao quero mais receber', 'nao enviar mais',
    ];

    public function whatsapp(): void
    {
        $expected = (string) getenv('AUTHENTICATION_API_KEY');
        $provided = (string) ($_GET['key'] ?? '');
        if ($expected === '' || !hash_equals($expected, $provided)) {
            http_response_code(403);
            return;
        }

        header('Content-Type: application/json');
        $payload = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            echo '{"ok":true}';
            return;
        }

        try {
            $this->handleMessage($payload);
        } catch (\Throwable $e) {
            error_log('Webhook WhatsApp: ' . $e->getMessage());
        }

        echo '{"ok":true}';
    }

    private function handleMessage(array $payload): void
    {
        $event = strtolower(str_replace('_', '.', (string) ($payload['event'] ?? '')));
        $data = $payload['data'] ?? null;
        if ($event !== 'messages.upsert' || !is_array($data)) {
            return;
        }

        $key = is_array($data['key'] ?? null) ? $data['key'] : [];
        if (!empty($key['fromMe'])) {
            return;
        }

        $message = is_array($data['message'] ?? null) ? $data['message'] : [];
        $text = $message['conversation'] ?? $message['extendedTextMessage']['text'] ?? '';
        if (!is_string($text) || !$this->isOptOutRequest($text)) {
            return;
        }

        $phone = $this->resolvePhone($key, $data);
        if ($phone === null) {
            error_log('Webhook WhatsApp: pedido de descadastro sem número identificável.');
            return;
        }

        $optOut = new WhatsappOptOut();
        $alreadyOptedOut = $optOut->isOptedOut($phone);
        $optOut->add($phone);
        (new ClienteOS())->markWhatsappUnavailableByPhone(WhatsappOptOut::key($phone));

        if ($alreadyOptedOut) {
            return;
        }

        $config = (new Configuracao())->get();
        if (!empty($config['evolution_enabled'])) {
            (new EvolutionApi(
                (string) ($config['evolution_url'] ?? ''),
                (string) ($config['evolution_api_key'] ?? ''),
                (string) ($config['evolution_instance'] ?? '')
            ))->sendText($phone, 'Tudo certo! Você não receberá mais mensagens da nossa pesquisa por WhatsApp.', 1500);
        }
    }

    private function isOptOutRequest(string $text): bool
    {
        $normalized = strtolower(trim($text));
        $normalized = strtr($normalized, [
            'ã' => 'a', 'á' => 'a', 'à' => 'a', 'â' => 'a', 'é' => 'e', 'ê' => 'e',
            'í' => 'i', 'ó' => 'o', 'õ' => 'o', 'ô' => 'o', 'ú' => 'u', 'ç' => 'c',
        ]);
        $normalized = trim(preg_replace('/[^a-z ]+/', '', $normalized) ?? '');
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? '';

        return in_array($normalized, self::OPT_OUT_WORDS, true);
    }

    private function resolvePhone(array $key, array $data): ?string
    {
        $candidates = [
            $key['remoteJidAlt'] ?? null,
            $key['senderPn'] ?? null,
            $data['senderPn'] ?? null,
            $key['remoteJid'] ?? null,
        ];

        foreach ($candidates as $jid) {
            if (!is_string($jid) || !str_ends_with($jid, '@s.whatsapp.net')) {
                continue;
            }

            try {
                return BrazilianPhoneNumber::normalize(strstr($jid, '@', true));
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }
}
