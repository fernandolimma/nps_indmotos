<?php
/**
 * @var array|null $pergunta
 * @var string $csrf_token
 */
?>
<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <h2><?= $pergunta ? 'Editar' : 'Nova' ?> Pergunta</h2>
        <a href="<?php echo App\Core\Router::baseUrl('/perguntas'); ?>" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="<?= $pergunta ? App\Core\Router::baseUrl('/perguntas/' . $pergunta['id'] . '/update') : App\Core\Router::baseUrl('/perguntas/store') ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= App\Core\View::escape($csrf_token) ?>">
                    
                    <div class="mb-3">
                        <label for="texto_pergunta" class="form-label">Texto da Pergunta</label>
                        <textarea class="form-control" id="texto_pergunta" name="texto_pergunta" rows="3" required><?= $pergunta ? App\Core\View::escape($pergunta['texto_pergunta']) : '' ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="escala_0_10" <?= $pergunta && $pergunta['tipo'] === 'escala_0_10' ? 'selected' : '' ?>>Escala 0-10</option>
                            <option value="discursiva" <?= $pergunta && $pergunta['tipo'] === 'discursiva' ? 'selected' : '' ?>>Discursiva</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="ordem" class="form-label">Ordem</label>
                        <input type="number" class="form-control" id="ordem" name="ordem" value="<?= $pergunta ? $pergunta['ordem'] : 0 ?>">
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="ativa" name="ativa" <?= !$pergunta || $pergunta['ativa'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ativa">Ativa</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = $pergunta ? 'Editar Pergunta' : 'Nova Pergunta';
require __DIR__ . '/../layouts/admin.php';
?>
