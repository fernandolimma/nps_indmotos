<?php
/**
 * @var string $csrf_token
 * @var int $pendentes_email
 * @var int $pendentes_whatsapp
 * @var array $registros_nao_enviados
 */
?>
<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <h2>Carga de Dados</h2>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Upload de Planilha</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo App\Core\Router::baseUrl('/carga/upload'); ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= App\Core\View::escape($csrf_token) ?>">
                    <div class="mb-3">
                        <label for="arquivo" class="form-label">Arquivo Excel (.xlsx)</label>
                        <input type="file" class="form-control" id="arquivo" name="arquivo" accept=".xlsx" required>
                        <div class="form-text">
                            Colunas necessárias: Tipo O.S., Nº O.S., Data Conclusão, Modelo, Pessoa, E-mail Pessoa. Para envio por WhatsApp, inclua Telefone Celular com DDD.
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Importar</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Disparo de Pesquisas</h5>
            </div>
            <div class="card-body">
                <p class="card-text">
                    <strong><?= $pendentes_email ?></strong> e-mails pendentes para disparo.
                    <?php if ($pendentes_whatsapp > 0): ?>
                        <br><strong><?= $pendentes_whatsapp ?></strong> WhatsApps pendentes.
                    <?php endif; ?>
                </p>
                <?php if ($pendentes_email > 0 || $pendentes_whatsapp > 0): ?>
                    <form action="<?php echo App\Core\Router::baseUrl('/carga/disparar'); ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= App\Core\View::escape($csrf_token) ?>">
                        <button type="submit" class="btn btn-success btn-lg w-100" onclick="return confirm('Deseja realmente disparar as pesquisas por e-mail e WhatsApp?')">
                            <i class="bi bi-send"></i> Disparar Pesquisas
                        </button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg w-100" disabled>
                        Nenhuma pesquisa pendente para disparo
                    </button>
                <?php endif; ?>

                <?php if ($registros_nao_enviados !== []): ?>
                    <hr>
                    <form action="<?php echo App\Core\Router::baseUrl('/carga/limpar-pendentes'); ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= App\Core\View::escape($csrf_token) ?>">
                        <button
                            type="submit"
                            class="btn btn-outline-danger w-100"
                            onclick="return confirm('Deseja realmente limpar os registros importados que ainda não tiveram envio por e-mail nem WhatsApp?')"
                        >
                            Limpar Dados Importados sem Envio
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center nps-section-meta">
                <div>
                    <h5 class="mb-1">Dados Importados Não Enviados</h5>
                    <p class="text-muted mb-0">Registros que ainda aguardam envio ou nova tentativa em pelo menos um canal.</p>
                </div>
                <span class="badge bg-secondary"><?= count($registros_nao_enviados) ?> registro(s)</span>
            </div>
            <div class="card-body">
                <?php if ($registros_nao_enviados !== []): ?>
                    <div class="nps-table-hint">Deslize a tabela horizontalmente para visualizar todas as colunas.</div>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>E-mail</th>
                                    <th>WhatsApp</th>
                                    <th>Status e-mail</th>
                                    <th>Status WhatsApp</th>
                                    <th>Modelo</th>
                                    <th>Tipo O.S.</th>
                                    <th>Nº O.S.</th>
                                    <th>Conclusão</th>
                                    <th>Importação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registros_nao_enviados as $registro): ?>
                                    <tr>
                                        <td><?= (int) $registro['id'] ?></td>
                                        <td><?= App\Core\View::escape((string) $registro['nome_cliente']) ?></td>
                                        <td><?= App\Core\View::escape((string) $registro['email_cliente']) ?></td>
                                        <td><?= App\Core\View::escape((string) ($registro['telefone_whatsapp'] ?? '')) ?></td>
                                        <td><?= App\Core\View::escape((string) $registro['status_email']) ?></td>
                                        <td><?= App\Core\View::escape((string) $registro['status_whatsapp']) ?></td>
                                        <td><?= App\Core\View::escape((string) ($registro['modelo_veiculo'] ?? '')) ?></td>
                                        <td><?= App\Core\View::escape((string) ($registro['tipo_os'] ?? '')) ?></td>
                                        <td><?= App\Core\View::escape((string) ($registro['numero_os'] ?? '')) ?></td>
                                        <td><?= !empty($registro['data_conclusao']) ? date('d/m/Y', strtotime((string) $registro['data_conclusao'])) : '-' ?></td>
                                        <td><?= !empty($registro['data_importacao']) ? date('d/m/Y H:i', strtotime((string) $registro['data_importacao'])) : '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Nenhum dado importado pendente de envio foi encontrado.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Carga';
require __DIR__ . '/../layouts/admin.php';
?>
