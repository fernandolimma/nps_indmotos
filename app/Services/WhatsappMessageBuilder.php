<?php

namespace App\Services;

class WhatsappMessageBuilder
{
    private const OPT_OUT_LINES = [
        'Se preferir não receber mais mensagens, responda SAIR.',
        'Para parar de receber nossas mensagens, é só responder SAIR.',
        'Não quer mais receber mensagens? Responda SAIR.',
        'Caso não queira receber mais contatos, responda SAIR a esta mensagem.',
    ];

    public static function build(array $cliente, string $empresa, string $link, ?\DateTimeInterface $agora = null): string
    {
        $agora ??= new \DateTimeImmutable();
        $nome = self::firstName((string) $cliente['nome_cliente']);
        $os = (string) $cliente['numero_os'];
        $data = date('d/m/Y', strtotime((string) $cliente['data_limite']));
        $saudacao = self::greeting((int) $agora->format('G'));

        $templates = [
            "Olá, {$nome}! Gostaríamos de ouvir sua opinião sobre o atendimento da {$empresa} referente à OS {$os}.\n\nResponda à pesquisa até {$data}:",
            "{$saudacao}, {$nome}! Aqui é da {$empresa}. Queremos saber como foi o atendimento da sua OS {$os}.\n\nSua resposta é rápida e vale até {$data}:",
            "Oi, {$nome}, tudo bem? A {$empresa} gostaria de saber o que você achou do serviço realizado na OS {$os}.\n\nA pesquisa fica disponível até {$data}:",
            "{$saudacao}, {$nome}! Sua opinião é muito importante para a {$empresa}. Pode nos contar como foi o atendimento da OS {$os}?\n\nÉ só responder até {$data}:",
            "Olá, {$nome}! Obrigado por escolher a {$empresa}. Para melhorarmos cada vez mais, conte como foi a OS {$os}.\n\nResponda até {$data}:",
        ];

        $corpo = $templates[random_int(0, count($templates) - 1)];
        $optOut = self::OPT_OUT_LINES[random_int(0, count(self::OPT_OUT_LINES) - 1)];

        return $corpo . "\n\n" . $link . "\n\n" . $optOut;
    }

    private static function firstName(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName));

        $first = $parts[0] !== '' ? $parts[0] : $fullName;

        return mb_convert_case($first, MB_CASE_TITLE, 'UTF-8');
    }

    private static function greeting(int $hour): string
    {
        if ($hour < 12) {
            return 'Bom dia';
        }

        return $hour < 18 ? 'Boa tarde' : 'Boa noite';
    }
}
