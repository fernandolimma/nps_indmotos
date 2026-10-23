<?php
/**
 * @var array $cliente
 * @var array $respostas
 */
?>
<?php ob_start(); ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="nps-page-header">
            <div>
                <h2 class="mb-1">Detalhes da Resposta</h2>
                <p class="text-muted mb-0">
                    Visualização completa das respostas do cliente ID <?= (int) $cliente['id'] ?>.
                </p>
            </div>
            <a href="<?php echo App\Core\Router::baseUrl('/relatorios'); ?>" class="btn btn-outline-secondary">
                Voltar para relatórios
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Dados do Cliente / OS</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <strong>ID:</strong><br>
                        <span><?= (int) $cliente['id'] ?></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Cliente:</strong><br>
                        <span><?= App\Core\View::escape($cliente['nome_cliente']) ?></span>
                    </div>
                    <div class="col-md-4">
                        <strong>E-mail:</strong><br>
                        <span><?= App\Core\View::escape($cliente['email_cliente']) ?></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Número OS:</strong><br>
                        <span><?= App\Core\View::escape($cliente['numero_os']) ?></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Tipo OS:</strong><br>
                        <span><?= App\Core\View::escape($cliente['tipo_os']) ?></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Veículo:</strong><br>
                        <span><?= App\Core\View::escape($cliente['modelo_veiculo']) ?></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Data de conclusão:</strong><br>
                        <span>
                            <?= !empty($cliente['data_conclusao']) ? date('d/m/Y', strtotime($cliente['data_conclusao'])) : '-' ?>
                        </span>
                    </div>
                    <div class="col-md-4">
                        <strong>Data de importação:</strong><br>
                        <span>
                            <?= !empty($cliente['data_importacao']) ? date('d/m/Y H:i', strtotime($cliente['data_importacao'])) : '-' ?>
                        </span>
                    </div>
                    <div class="col-md-4">
                        <strong>Status da resposta:</strong><br>
                        <span class="badge bg-success"><?= App\Core\View::escape((string) $cliente['status_resposta']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Respostas da Pesquisa</h5>
            </div>
            <div class="card-body">
                <?php foreach ($respostas as $index => $resposta): ?>
                    <div class="<?= $index > 0 ? 'border-top pt-3 mt-3' : '' ?>">
                        <div class="mb-2">
                            <strong>Pergunta <?= $index + 1 ?>:</strong>
                        </div>
                        <p class="mb-2"><?= App\Core\View::escape($resposta['texto_pergunta']) ?></p>

                        <div class="bg-light rounded p-3">
                            <?php if ($resposta['nota'] !== null): ?>
                                <span class="badge bg-primary fs-6">Nota: <?= (int) $resposta['nota'] ?></span>
                            <?php endif; ?>

                            <?php if (!empty($resposta['resposta_texto'])): ?>
                                <p class="<?= $resposta['nota'] !== null ? 'mt-3 mb-0' : 'mb-0' ?>">
                                    <?= nl2br(App\Core\View::escape($resposta['resposta_texto'])) ?>
                                </p>
                            <?php elseif ($resposta['nota'] === null): ?>
                                <span class="text-muted">Resposta não informada.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Detalhe do Relatório';
require __DIR__ . '/../layouts/admin.php';
?>
