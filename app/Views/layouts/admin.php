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
    <meta name="theme-color" content="#f3f5f7">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= App\Core\Router::baseUrl('/css/nps-theme.css') ?>" rel="stylesheet">
</head>
<body class="nps-admin-page">
    <nav class="navbar navbar-expand-lg navbar-light nps-admin-navbar">
        <div class="container nps-admin-container">
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

    <div class="container mt-4 nps-admin-container nps-admin-content">
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
