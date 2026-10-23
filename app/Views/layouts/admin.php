<?php
/**
 * @var string $title
 * @var string $content
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? App\Core\View::escape($title) . ' - ' : '' ?><?= App\Core\View::escape(App\Core\View::appName()) ?></title>
    <link rel="icon" type="image/png" sizes="192x192" href="<?= App\Core\Router::baseUrl('/imagens/nps_indmotos_192x192.png') ?>">
    <link rel="icon" type="image/png" sizes="500x500" href="<?= App\Core\Router::baseUrl('/imagens/nps_indmotos_500x500.png') ?>">
    <link rel="apple-touch-icon" sizes="192x192" href="<?= App\Core\Router::baseUrl('/imagens/nps_indmotos_192x192.png') ?>">
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            overflow-x: hidden;
        }

        .nps-table-hint {
            display: none;
        }

        .table-responsive {
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 767.98px) {
            .container {
                padding-left: 12px;
                padding-right: 12px;
            }

            .navbar-brand {
                font-size: 1rem;
            }

            .card-body {
                padding: 1rem;
            }

            .table {
                min-width: 640px;
            }

            .pagination {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .page-link {
                border-radius: 0.5rem;
            }

            .nps-table-hint {
                display: block;
                font-size: 0.875rem;
                color: #6c757d;
                margin-bottom: 0.75rem;
            }

            .nps-page-header {
                display: flex;
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
            }

            .nps-page-header .btn {
                width: 100%;
            }

            .nps-filter-actions {
                flex-direction: column;
                align-items: stretch !important;
            }

            .nps-filter-actions .btn {
                width: 100%;
            }

            .nps-section-meta {
                display: flex;
                flex-direction: column;
                align-items: flex-start !important;
                gap: 0.25rem;
            }

            .nps-mobile-stack > * + * {
                margin-top: 0.5rem;
            }

            .nps-stat-card {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo App\Core\Router::baseUrl('/dashboard'); ?>">NPS Indmotos</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo App\Core\Router::baseUrl('/dashboard'); ?>">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo App\Core\Router::baseUrl('/carga'); ?>">Carga</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo App\Core\Router::baseUrl('/perguntas'); ?>">Perguntas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo App\Core\Router::baseUrl('/relatorios'); ?>">Relatórios</a>
                    </li>
                    <?php if (App\Core\Session::get('usuario_tipo') === 'MASTER'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo App\Core\Router::baseUrl('/configuracoes'); ?>">Configurações</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo App\Core\Router::baseUrl('/logout'); ?>">Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($success = App\Core\Session::flash('success')): ?>
            <div class="alert alert-success"><?= App\Core\View::escape($success) ?></div>
        <?php endif; ?>
        <?php if ($error = App\Core\Session::flash('error')): ?>
            <div class="alert alert-danger"><?= App\Core\View::escape($error) ?></div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
