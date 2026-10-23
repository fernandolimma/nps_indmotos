<?php
/**
 * @var array $cliente
 * @var array $perguntas
 * @var string $csrf_token
 * @var string|null $logo_url
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisa de Satisfação - <?= App\Core\View::escape(App\Core\View::appName()) ?></title>
    <link rel="icon" type="image/png" sizes="192x192" href="<?= App\Core\Router::baseUrl('/imagens/nps_indmotos_192x192.png') ?>">
    <link rel="icon" type="image/png" sizes="500x500" href="<?= App\Core\Router::baseUrl('/imagens/nps_indmotos_500x500.png') ?>">
    <link rel="apple-touch-icon" sizes="192x192" href="<?= App\Core\Router::baseUrl('/imagens/nps_indmotos_192x192.png') ?>">
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .nps-score-grid .form-check {
            min-width: 2.5rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            background: #fff;
        }

        @media (max-width: 767.98px) {
            .nps-public-shell {
                margin-top: 1rem !important;
                margin-bottom: 1rem !important;
            }

            .nps-public-card .card-body,
            .nps-public-card .card-header {
                padding: 1rem;
            }

            .nps-os-summary .col-md-6 + .col-md-6,
            .nps-os-summary .col-md-6 + .col-12,
            .nps-os-summary .col-12 + .col-md-6 {
                margin-top: 0.5rem;
            }

            .nps-score-grid {
                gap: 0.5rem !important;
            }

            .nps-score-grid .form-check {
                width: calc(33.333% - 0.5rem);
                margin: 0;
                text-align: center;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5 mb-5 nps-public-shell">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card nps-public-card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Pesquisa de Satisfação</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($logo_url)): ?>
                            <div class="text-center mb-4">
                                <img src="<?= App\Core\View::escape($logo_url) ?>" alt="Logo" style="max-height: 90px; max-width: 100%;">
                            </div>
                        <?php endif; ?>
                        <div class="alert alert-info mb-4">
                            <h6>Dados da OS:</h6>
                            <div class="row nps-os-summary">
                                <div class="col-md-6"><strong>Cliente:</strong> <?= App\Core\View::escape($cliente['nome_cliente']) ?></div>
                                <div class="col-md-6"><strong>Atendimento ID:</strong> <?= $cliente['id'] ?></div>
                                <div class="col-md-6"><strong>Veículo:</strong> <?= App\Core\View::escape($cliente['modelo_veiculo']) ?></div>
                                <div class="col-md-6"><strong>Tipo OS:</strong> <?= App\Core\View::escape($cliente['tipo_os']) ?></div>
                                <div class="col-md-6"><strong>Número OS:</strong> <?= App\Core\View::escape($cliente['numero_os']) ?></div>
                                <div class="col-md-6"><strong>Data Conclusão:</strong> <?= $cliente['data_conclusao'] ? date('d/m/Y', strtotime($cliente['data_conclusao'])) : '' ?></div>
                            </div>
                        </div>

                        <form action="<?php echo App\Core\Router::baseUrl('/pesquisa/submit'); ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= App\Core\View::escape($csrf_token) ?>">
                            <input type="hidden" name="token" value="<?= App\Core\View::escape($cliente['token_unico']) ?>">

                            <?php foreach ($perguntas as $pergunta): ?>
                                <div class="mb-4">
                                    <label class="form-label fw-bold"><?= App\Core\View::escape($pergunta['texto_pergunta']) ?></label>
                                    
                                    <?php if ($pergunta['tipo'] === 'escala_0_10'): ?>
                                        <div class="d-flex flex-wrap gap-2 mt-2 nps-score-grid">
                                            <?php for ($i = 0; $i <= 10; $i++): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="pergunta_<?= $pergunta['id'] ?>" id="pergunta_<?= $pergunta['id'] ?>_<?= $i ?>" value="<?= $i ?>" required>
                                                    <label class="form-check-label" for="pergunta_<?= $pergunta['id'] ?>_<?= $i ?>">
                                                        <?= $i ?>
                                                    </label>
                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                    <?php else: ?>
                                        <textarea class="form-control" name="pergunta_<?= $pergunta['id'] ?>" rows="4" placeholder="Sua resposta..."></textarea>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Enviar Resposta</button>
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
