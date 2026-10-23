<?php
/**
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuração Inicial - <?= App\Core\View::escape(App\Core\View::appName()) ?></title>
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
            <div class="col-md-5">
                <div class="card nps-auth-card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Configuração Inicial</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">Crie o usuário administrador inicial.</p>
                        <form action="<?php echo App\Core\Router::baseUrl('/setup'); ?>" method="POST">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" required minlength="6">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Criar Usuário</button>
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
