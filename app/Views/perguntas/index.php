<?php
/**
 * @var array $perguntas
 */
?>
<?php ob_start(); ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="nps-page-header">
            <h2 class="mb-0">Perguntas</h2>
            <a href="<?php echo App\Core\Router::baseUrl('/perguntas/create'); ?>" class="btn btn-primary">Nova Pergunta</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Ordem</th>
                            <th>Pergunta</th>
                            <th>Tipo</th>
                            <th>Ativa</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($perguntas as $pergunta): ?>
                            <tr>
                                <td><?= $pergunta['ordem'] ?></td>
                                <td><?= App\Core\View::escape($pergunta['texto_pergunta']) ?></td>
                                <td><?= $pergunta['tipo'] === 'escala_0_10' ? 'Escala 0-10' : 'Discursiva' ?></td>
                                <td><?= $pergunta['ativa'] ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-secondary">Não</span>' ?></td>
                                <td class="nps-mobile-stack">
                                    <a href="<?php echo App\Core\Router::baseUrl('/perguntas/' . $pergunta['id'] . '/edit'); ?>" class="btn btn-sm btn-primary">Editar</a>
                                    <a href="<?php echo App\Core\Router::baseUrl('/perguntas/' . $pergunta['id'] . '/delete'); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Remover</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Perguntas';
require __DIR__ . '/../layouts/admin.php';
?>
