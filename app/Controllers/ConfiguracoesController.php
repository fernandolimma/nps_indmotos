<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\View;
use App\Models\Configuracao;
use App\Models\Usuario;

class ConfiguracoesController extends Controller
{
    private Configuracao $configuracao;
    private Usuario $usuario;

    public function __construct()
    {
        $this->requireMaster();
        $this->configuracao = new Configuracao();
        $this->usuario = new Usuario();
    }

    public function index(): void
    {
        $config = $this->configuracao->get();
        $usuarios = $this->usuario->getAll();
        View::render('configuracoes/index', [
            'config' => $config,
            'usuarios' => $usuarios,
            'csrf_token' => $this->generateCsrfToken(),
            'logo_url' => $this->resolveLogoUrl($config)
        ]);
    }

    public function save(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/configuracoes');
        }

        if (!$this->validateCsrfToken($this->getPost('csrf_token'))) {
            Session::flash('error', 'Token inválido');
            $this->redirect('/configuracoes');
        }

        try {
            $configAtual = $this->configuracao->get();
            $logoAtual = $configAtual['logo_path'] ?? null;
            $logoPath = $this->processLogoUpload($configAtual['logo_path'] ?? null);

            $this->configuracao->update([
                'smtp_host' => $this->getPost('smtp_host'),
                'smtp_port' => (int)$this->getPost('smtp_port'),
                'smtp_usuario' => $this->getPost('smtp_usuario'),
                'smtp_senha' => $this->getPost('smtp_senha'),
                'email_remetente' => $this->getPost('email_remetente'),
                'nome_remetente' => $this->getPost('nome_remetente'),
                'logo_path' => $logoPath,
                'dias_validade' => (int)$this->getPost('dias_validade'),
                'evolution_enabled' => $this->getPost('evolution_enabled') ? 1 : 0,
                'evolution_url' => trim((string) $this->getPost('evolution_url', '')),
                'evolution_instance' => trim((string) $this->getPost('evolution_instance', '')),
                'evolution_api_key' => trim((string) $this->getPost('evolution_api_key', '')) !== ''
                    ? trim((string) $this->getPost('evolution_api_key'))
                    : ($configAtual['evolution_api_key'] ?? null)
            ]);

            if (!empty($logoAtual) && $logoAtual !== $logoPath) {
                $this->deleteLogoFile($logoAtual);
            }

            Session::flash('success', 'Configurações salvas com sucesso!');
        } catch (\Throwable $e) {
            Session::flash('error', 'Erro ao salvar configurações: ' . $e->getMessage());
        }

        $this->redirect('/configuracoes');
    }

    public function createUser(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/configuracoes');
        }

        if (!$this->validateCsrfToken($this->getPost('csrf_token'))) {
            Session::flash('error', 'Token inválido');
            $this->redirect('/configuracoes');
        }

        $this->usuario->create([
            'nome' => $this->getPost('nome'),
            'email' => $this->getPost('email'),
            'senha' => $this->getPost('senha'),
            'tipo_usuario' => $this->getPost('tipo_usuario', 'USUARIO')
        ]);

        Session::flash('success', 'Usuário criado com sucesso!');
        $this->redirect('/configuracoes');
    }

    public function deleteUser(int $id): void
    {
        $this->usuario->delete($id);
        Session::flash('success', 'Usuário removido com sucesso!');
        $this->redirect('/configuracoes');
    }

    private function resolveLogoUrl(?array $config): ?string
    {
        if (empty($config['logo_path'])) {
            return null;
        }

        return \App\Core\Router::baseUrl('/' . ltrim(str_replace('\\', '/', $config['logo_path']), '/'));
    }

    private function processLogoUpload(?string $currentLogoPath): ?string
    {
        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] === UPLOAD_ERR_NO_FILE) {
            return $currentLogoPath;
        }

        if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Falha no upload da logo.');
        }

        $file = $_FILES['logo'];
        $maxSize = 2 * 1024 * 1024;

        if (($file['size'] ?? 0) > $maxSize) {
            throw new \RuntimeException('A logo deve ter no máximo 2 MB.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = (string) $finfo->file($file['tmp_name']);
        $allowedTypes = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];

        if (!isset($allowedTypes[$mimeType]) || @getimagesize($file['tmp_name']) === false) {
            throw new \RuntimeException('Formato de logo inválido. Use PNG, JPG, GIF ou WEBP.');
        }

        $uploadDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'logos';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            throw new \RuntimeException('Não foi possível preparar a pasta da logo.');
        }

        $fileName = 'logo_' . bin2hex(random_bytes(8)) . '.' . $allowedTypes[$mimeType];
        $destination = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException('Não foi possível salvar a logo enviada.');
        }

        return 'uploads/logos/' . $fileName;
    }

    private function deleteLogoFile(string $logoPath): void
    {
        $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $logoPath);
        $filePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $normalizedPath;

        if (is_file($filePath) && strpos($normalizedPath, 'uploads' . DIRECTORY_SEPARATOR . 'logos' . DIRECTORY_SEPARATOR) === 0) {
            @unlink($filePath);
        }
    }
}
