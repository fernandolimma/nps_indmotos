<?php
/**
 * @var array $config
 * @var array $usuarios
 * @var string $csrf_token
 * @var string|null $logo_url
 * @var string|null $evolution_qr
 * @var string|null $evolution_pairing_code
 */
?>
<?php ob_start(); ?>

<div class="row mb-4">
    <div class="col-12">
        <h2>Configurações</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Configurações de E-mail</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo App\Core\Router::baseUrl('/configuracoes/save'); ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= App\Core\View::escape($csrf_token) ?>">

                    <div class="mb-3">
                        <label for="logo" class="form-label">Logo</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept=".png,.jpg,.jpeg,.gif,.webp,image/png,image/jpeg,image/gif,image/webp">
                        <div class="form-text">Formatos aceitos: PNG, JPG, GIF e WEBP. Tamanho máximo: 2 MB.</div>
                    </div>

                    <?php if (!empty($logo_url)): ?>
                        <div class="mb-3">
                            <label class="form-label d-block">Logo atual</label>
                            <div class="border rounded p-3 bg-light text-center">
                                <img src="<?= App\Core\View::escape($logo_url) ?>" alt="Logo atual" style="max-height: 80px; max-width: 100%;">
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="smtp_host" class="form-label">SMTP Host</label>
                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?= App\Core\View::escape($config['smtp_host'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="smtp_port" class="form-label">SMTP Porta</label>
                        <input type="number" class="form-control" id="smtp_port" name="smtp_port" value="<?= $config['smtp_port'] ?? 587 ?>">
                    </div>

                    <div class="mb-3">
                        <label for="smtp_usuario" class="form-label">SMTP Usuário</label>
                        <input type="text" class="form-control" id="smtp_usuario" name="smtp_usuario" value="<?= App\Core\View::escape($config['smtp_usuario'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="smtp_senha" class="form-label">SMTP Senha</label>
                        <input type="password" class="form-control" id="smtp_senha" name="smtp_senha" value="<?= App\Core\View::escape($config['smtp_senha'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email_remetente" class="form-label">E-mail Remetente</label>
                        <input type="email" class="form-control" id="email_remetente" name="email_remetente" value="<?= App\Core\View::escape($config['email_remetente'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="nome_remetente" class="form-label">Nome Remetente</label>
                        <input type="text" class="form-control" id="nome_remetente" name="nome_remetente" value="<?= App\Core\View::escape($config['nome_remetente'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="dias_validade" class="form-label">Dias de Validade da Pesquisa</label>
                        <input type="number" class="form-control" id="dias_validade" name="dias_validade" value="<?= $config['dias_validade'] ?? 7 ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">WhatsApp (Evolution API)</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($config['evolution_enabled'])): ?>
                    <p><span class="badge bg-success">Envio habilitado</span> Pesquisas também serão enviadas pelo WhatsApp.</p>
                <?php else: ?>
                    <p><span class="badge bg-secondary">Envio desabilitado</span> Conecte o WhatsApp para habilitar o envio.</p>
                <?php endif; ?>

                <?php if (empty($config['evolution_instance'])): ?>
                    <form action="<?php echo App\Core\Router::baseUrl('/configuracoes/evolution/create-instance'); ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= App\Core\View::escape($csrf_token) ?>">
                        <div class="mb-3">
                            <label for="evolution_instance" class="form-label">Nome da instância</label>
                            <input
                                type="text"
                                class="form-control"
                                id="evolution_instance"
                                name="evolution_instance"
                                value="nps-indmotos"
                                pattern="[A-Za-z0-9_-]{1,100}"
                                required
                            >
                        </div>
                        <button type="submit" class="btn btn-primary">Criar conexão e gerar QR code</button>
                    </form>
                <?php else: ?>
                    <p>Instância: <strong><?= App\Core\View::escape($config['evolution_instance']) ?></strong></p>

                    <?php if (!empty($evolution_qr) || !empty($evolution_pairing_code)): ?>
                        <div class="border rounded p-3 text-center mb-3">
                            <p class="mb-2">No celular, abra o WhatsApp em <strong>Dispositivos conectados &gt; Conectar dispositivo</strong> e escaneie este código:</p>
                            <?php if (!empty($evolution_qr)): ?>
                                <img
                                    src="<?= App\Core\View::escape($evolution_qr) ?>"
                                    alt="QR code para conectar o WhatsApp"
                                    class="img-fluid"
                                    style="max-width: 280px;"
                                >
                            <?php endif; ?>
                            <?php if (!empty($evolution_pairing_code)): ?>
                                <p class="mb-0"><strong>Código de pareamento:</strong> <?= App\Core\View::escape($evolution_pairing_code) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex flex-wrap gap-2">
                        <form action="<?php echo App\Core\Router::baseUrl('/configuracoes/evolution/refresh-qr'); ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= App\Core\View::escape($csrf_token) ?>">
                            <button type="submit" class="btn btn-outline-primary">
                                <?= !empty($evolution_qr) || !empty($evolution_pairing_code) ? 'Gerar outro QR code' : 'Conectar ou gerar novo QR code' ?>
                            </button>
                        </form>
                        <?php if (!empty($evolution_qr) || !empty($evolution_pairing_code)): ?>
                            <form action="<?php echo App\Core\Router::baseUrl('/configuracoes/evolution/confirm-connection'); ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= App\Core\View::escape($csrf_token) ?>">
                                <button type="submit" class="btn btn-primary">Já conectei: ativar WhatsApp</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <h5 class="mb-0">Usuários</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#novoUsuarioModal">Novo Usuário</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Tipo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= App\Core\View::escape($usuario['nome']) ?></td>
                                <td><?= App\Core\View::escape($usuario['email']) ?></td>
                                <td>
                                    <span class="badge <?= ($usuario['tipo_usuario'] ?? 'USUARIO') === 'MASTER' ? 'bg-primary' : 'bg-secondary' ?>">
                                        <?= App\Core\View::escape($usuario['tipo_usuario'] ?? 'USUARIO') ?>
                                    </span>
                                </td>
                                <td class="nps-mobile-stack">
                                    <a href="<?php echo App\Core\Router::baseUrl('/configuracoes/' . $usuario['id'] . '/delete-user'); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Remover</a>
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

<div class="modal fade" id="novoUsuarioModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form action="<?php echo App\Core\Router::baseUrl('/configuracoes/create-user'); ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= App\Core\View::escape($csrf_token) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
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
                    <div class="mb-3">
                        <label for="tipo_usuario" class="form-label">Tipo de Usuário</label>
                        <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                            <option value="USUARIO">USUARIO</option>
                            <option value="MASTER">MASTER</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Configurações';
require __DIR__ . '/../layouts/admin.php';
?>
