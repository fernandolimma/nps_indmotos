<?php
/**
 * @var string $mensagem
 * @var string|null $logo_url
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro - <?= App\Core\View::escape(App\Core\View::appName()) ?></title>
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
                            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="#dc3545" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16">
                                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.768.98 1.768h12.81a1 1 0 0 0 .969-1.318L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                            </svg>
                        </div>
                        <h2 class="text-danger mb-3">Ops!</h2>
                        <p class="lead"><?= App\Core\View::escape($mensagem) ?>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
