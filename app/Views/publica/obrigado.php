<?php
/**
 * @var string|null $logo_url
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obrigado! - <?= App\Core\View::escape(App\Core\View::appName()) ?></title>
    <link rel="icon" type="image/png" sizes="192x192" href="<?= App\Core\Router::baseUrl('/imagens/nps_indmotos_192x192.png') ?>">
    <link rel="icon" type="image/png" sizes="500x500" href="<?= App\Core\Router::baseUrl('/imagens/nps_indmotos_500x500.png') ?>">
    <link rel="apple-touch-icon" sizes="192x192" href="<?= App\Core\Router::baseUrl('/imagens/nps_indmotos_192x192.png') ?>">
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media (max-width: 767.98px) {
            .nps-public-shell {
                margin-top: 1rem !important;
            }

            .nps-public-card .card-body {
                padding: 1.5rem 1rem !important;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5 nps-public-shell">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="card nps-public-card">
                    <div class="card-body py-5">
                        <?php if (!empty($logo_url)): ?>
                            <div class="mb-4">
                                <img src="<?= App\Core\View::escape($logo_url) ?>" alt="Logo" style="max-height: 90px; max-width: 100%;">
                            </div>
                        <?php endif; ?>
                        <div class="mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="#198754" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.021-1.06z"/>
                            </svg>
                        </div>
                        <h2 class="text-success mb-3">Obrigado pela sua resposta!</h2>
                        <p class="lead">Sua opinião é muito importante para nós.</p>
                        <p class="lead">Contato: (32) 98703-4917.</p>
                        <p class="text-muted">Atenciosamente,<br>Equipe Independência Motos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
