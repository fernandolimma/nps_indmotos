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
    <title>Login - <?= App\Core\View::escape(App\Core\View::appName()) ?></title>
    <link rel="icon" type="image/png" sizes="192x192" href="<?= App\Core\Router::baseUrl('/imagens/nps_indmotos_192x192.png') ?>">
    <link rel="icon" type="image/png" sizes="500x500" href="<?= App\Core\Router::baseUrl('/imagens/nps_indmotos_500x500.png') ?>">
    <link rel="apple-touch-icon" sizes="192x192" href="<?= App\Core\Router::baseUrl('/imagens/nps_indmotos_192x192.png') ?>">
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media (max-width: 767.98px) {
            .nps-auth-shell {
                margin-top: 1.5rem !important;
                padding-bottom: 1.5rem;
            }

            .nps-auth-card .card-body,
            .nps-auth-card .card-header {
                padding: 1rem;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5 nps-auth-shell">
            <div class="col-md-4">
                <div class="card nps-auth-card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($logo_url)): ?>
                            <div class="text-center mb-4">
                                <img src="<?= App\Core\View::escape($logo_url) ?>" alt="Logo" style="max-height: 90px; max-width: 100%;">
                            </div>
                        <?php endif; ?>
                        <?php if ($error = App\Core\Session::flash('error')): ?>
                            <div class="alert alert-danger"><?= App\Core\View::escape($error) ?></div>
                        <?php endif; ?>
                        <?php if ($success = App\Core\Session::flash('success')): ?>
                            <div class="alert alert-success"><?= App\Core\View::escape($success) ?></div>
                        <?php endif; ?>
                        <form action="<?php echo App\Core\Router::baseUrl('/login/authenticate'); ?>" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Entrar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
