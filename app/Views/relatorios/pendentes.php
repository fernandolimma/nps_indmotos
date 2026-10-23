<?php
/**
 * @var array $items
 * @var int $total
 * @var int $pages
 * @var int $current_page
 * @var string|null $data_inicial
 * @var string|null $data_final
 */
?>
<?php ob_start(); ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="nps-page-header">
            <div>
                <h2 class="mb-1">E-mails Não Respondidos</h2>
                <p class="text-muted mb-0">Lista de pesquisas que ainda aguardam resposta do cliente.</p>
            </div>
            <a href="<?php echo App\Core\Router::baseUrl('/relatorios'); ?>" class="btn btn-outline-secondary">
                Voltar para relatórios
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?php echo App\Core\Router::baseUrl('/relatorios/pendentes'); ?>" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="data_inicial" class="form-label">Data inicial</label>
                        <input type="date" class="form-control" id="data_inicial" name="data_inicial" value="<?= App\Core\View::escape($data_inicial ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="data_final" class="form-label">Data final</label>
                        <input type="date" class="form-control" id="data_final" name="data_final" value="<?= App\Core\View::escape($data_final ?? '') ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2 nps-filter-actions">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="<?php echo App\Core\Router::baseUrl('/relatorios/pendentes'); ?>" class="btn btn-outline-secondary">Limpar</a>
                    </div>
                </form>

                <div class="d-flex justify-content-between align-items-center mb-3 nps-section-meta">
                    <span class="text-muted">Total de registros: <?= $total ?></span>
                    <span class="text-muted">20 registros por página</span>
                </div>

                <div class="nps-table-hint">Deslize a tabela horizontalmente para visualizar todas as colunas.</div>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>E-mail</th>
                                <th>Número OS</th>
                                <th>Status do E-mail</th>
                                <th>Data Limite</th>
                                <th>Data Importação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($items !== []): ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?= (int) $item['id'] ?></td>
                                        <td><?= App\Core\View::escape($item['nome_cliente']) ?></td>
                                        <td><?= App\Core\View::escape($item['email_cliente']) ?></td>
                                        <td><?= App\Core\View::escape($item['numero_os']) ?></td>
                                        <td>
                                            <span class="badge <?= $item['status_email'] === 'enviado' ? 'bg-info text-dark' : 'bg-secondary' ?>">
                                                <?= App\Core\View::escape($item['status_email']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= !empty($item['data_limite']) ? date('d/m/Y', strtotime($item['data_limite'])) : '-' ?>
                                        </td>
                                        <td>
                                            <?= !empty($item['data_importacao']) ? date('d/m/Y H:i', strtotime($item['data_importacao'])) : '-' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Nenhum e-mail pendente de resposta foi encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($pages > 1): ?>
                    <?php
                    $queryBase = [];
                    if (!empty($data_inicial)) {
                        $queryBase['data_inicial'] = $data_inicial;
                    }
                    if (!empty($data_final)) {
                        $queryBase['data_final'] = $data_final;
                    }
                    ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php if ($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($queryBase, ['page' => $current_page - 1])) ?>">Anterior</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $pages; $i++): ?>
                                <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($queryBase, ['page' => $i])) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($current_page < $pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($queryBase, ['page' => $current_page + 1])) ?>">Próxima</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'E-mails Não Respondidos';
require __DIR__ . '/../layouts/admin.php';
?>
