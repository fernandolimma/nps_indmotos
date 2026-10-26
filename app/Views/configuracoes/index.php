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

                    <hr>
                    <h5>WhatsApp (Evolution API)</h5>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="evolution_enabled" name="evolution_enabled" value="1" <?= !empty($config['evolution_enabled']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="evolution_enabled">Enviar pesquisas pelo WhatsApp junto com o e-mail</label>
                    </div>
                    <div class="mb-3">
                        <label for="evolution_url" class="form-label">URL base da Evolution API</label>
                        <input type="url" class="form-control" id="evolution_url" name="evolution_url" value="<?= App\Core\View::escape($config['evolution_url'] ?? '') ?>" placeholder="https://evolution.exemplo.com">
                    </div>
                    <div class="mb-3">
                        <label for="evolution_instance" class="form-label">Instância</label>
                        <input type="text" class="form-control" id="evolution_instance" name="evolution_instance" value="<?= App\Core\View::escape($config['evolution_instance'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="evolution_api_key" class="form-label">API key</label>
                        <input type="password" class="form-control" id="evolution_api_key" name="evolution_api_key" value="" autocomplete="new-password">
                        <div class="form-text">Deixe em branco para manter a chave salva.</div>
                    </div>

                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <button
                        type="submit"
                        class="btn btn-outline-primary"
                        formaction="<?php echo App\Core\Router::baseUrl('/configuracoes/test-evolution'); ?>"
                    >
                        Testar conexão da Evolution API
                    </button>
                </form>

                <hr>
                <h6>Instalação local com Docker</h6>
                <p class="text-muted">
                    Inicie o Docker Desktop e execute <code>.\docker\setup.ps1</code> no PowerShell, na pasta do projeto.
                    Depois, informe o nome da instância e crie a conexão abaixo.
                </p>
                <form action="<?php echo App\Core\Router::baseUrl('/configuracoes/evolution/create-instance'); ?>" method="POST" class="mb-3">
                    <input type="hidden" name="csrf_token" value="<?= App\Core\View::escape($csrf_token) ?>">
                    <label for="evolution_instance_docker" class="form-label">Nome da instância WhatsApp</label>
                    <input
                        type="text"
                        class="form-control mb-2"
                        id="evolution_instance_docker"
                        name="evolution_instance"
                        value="<?= App\Core\View::escape($config['evolution_instance'] ?? 'nps-indmotos') ?>"
                        pattern="[A-Za-z0-9_-]{1,100}"
                        required
                    >
                    <button type="submit" class="btn btn-success">Criar instância e gerar QR code</button>
                </form>

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
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <form action="<?php echo App\Core\Router::baseUrl('/configuracoes/evolution/refresh-qr'); ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= App\Core\View::escape($csrf_token) ?>">
                            <input type="hidden" name="evolution_instance" value="<?= App\Core\View::escape($config['evolution_instance'] ?? 'nps-indmotos') ?>">
                            <button type="submit" class="btn btn-outline-secondary">Gerar outro QR code</button>
                        </form>
                        <form action="<?php echo App\Core\Router::baseUrl('/configuracoes/evolution/confirm-connection'); ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= App\Core\View::escape($csrf_token) ?>">
                            <input type="hidden" name="evolution_instance" value="<?= App\Core\View::escape($config['evolution_instance'] ?? 'nps-indmotos') ?>">
                            <button type="submit" class="btn btn-primary">Já conectei: verificar e ativar WhatsApp</button>
                        </form>
                    </div>
                <?php else: ?>
                    <form action="<?php echo App\Core\Router::baseUrl('/configuracoes/evolution/refresh-qr'); ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= App\Core\View::escape($csrf_token) ?>">
                        <input type="hidden" name="evolution_instance" value="<?= App\Core\View::escape($config['evolution_instance'] ?? 'nps-indmotos') ?>">
                        <button type="submit" class="btn btn-outline-secondary">Gerar QR code para instância existente</button>
                    </form>
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
