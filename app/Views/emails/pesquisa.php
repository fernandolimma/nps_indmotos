<?php
/**
 * @var array $cliente
 * @var string $link
 * @var string|null $logo_url
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <?php if (!empty($logo_url)): ?>
            <div style="text-align: center; margin-bottom: 24px;">
                <img src="<?= App\Core\View::escape($logo_url) ?>" alt="Logo" style="max-height: 90px; max-width: 100%;">
            </div>
        <?php endif; ?>
        <h2 style="color: #0d6efd;">Olá, <?= App\Core\View::escape($cliente['nome_cliente']) ?>!</h2>
        <p>Gostaríamos de ouvir sua opinião sobre o atendimento recebido na <?= App\Core\View::escape(App\Core\View::appName()) ?>.</p>
        <p><strong>OS: <?= App\Core\View::escape($cliente['numero_os']) ?></strong></p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="<?= $link ?>" style="background-color: #0d6efd; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 18px;">
                Responder Pesquisa
            </a>
        </p>
        <p style="font-size: 12px; color: #666;">
            Este link é válido até <?= date('d/m/Y', strtotime($cliente['data_limite'])) ?>.
        </p>
        <hr>
        <p style="font-size: 12px; color: #666;">
            Atenciosamente,<br>
            Equipe <?= App\Core\View::escape(App\Core\View::appName()) ?>
        </p>
    </div>
</body>
</html>
