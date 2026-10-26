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
            'logo_url' => $this->resolveLogoUrl($config),
            'evolution_qr' => Session::get('evolution_qr'),
            'evolution_pairing_code' => Session::get('evolution_pairing_code')
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

    public function testEvolution(): void
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
            $apiKey = trim((string) $this->getPost('evolution_api_key', ''));
            if ($apiKey === '') {
                $apiKey = (string) ($configAtual['evolution_api_key'] ?? '');
            }

            $evolution = new \App\Services\EvolutionApi(
                trim((string) $this->getPost('evolution_url', '')),
                $apiKey,
                trim((string) $this->getPost('evolution_instance', ''))
            );
            Session::flash('success', $evolution->checkConnection());
        } catch (\Throwable $e) {
            Session::flash('error', 'Falha ao validar a Evolution API: ' . $e->getMessage());
        }

        $this->redirect('/configuracoes');
    }

    public function createEvolutionInstance(): void
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
            $instance = $this->getEvolutionInstanceName($configAtual);
            $evolution = $this->getLocalEvolutionApi($instance);
            $this->saveLocalEvolutionSettings($instance);
            $evolution->createInstance();
            $qrCode = $evolution->getQrCode();
            Session::set('evolution_qr', $qrCode['base64']);
            Session::set('evolution_pairing_code', $qrCode['code']);
            Session::flash('success', 'Instância criada. Escaneie o QR code com o WhatsApp para conectar.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Não foi possível criar a instância ou gerar o QR code: ' . $e->getMessage());
        }

        $this->redirect('/configuracoes');
    }

    public function refreshEvolutionQr(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/configuracoes');
        }

        if (!$this->validateCsrfToken($this->getPost('csrf_token'))) {
            Session::flash('error', 'Token inválido');
            $this->redirect('/configuracoes');
        }

        try {
            $instance = $this->getEvolutionInstanceName($this->configuracao->get());
            $evolution = $this->getLocalEvolutionApi($instance);
            $this->saveLocalEvolutionSettings($instance);
            if (strtolower($evolution->getConnectionState()) === 'open') {
                $this->configuracao->update([
                    'evolution_enabled' => 1,
                    'evolution_url' => 'http://127.0.0.1:8080',
                    'evolution_instance' => $instance,
                    'evolution_api_key' => $this->getLocalEvolutionApiKey()
                ]);
                Session::remove('evolution_qr');
                Session::remove('evolution_pairing_code');
                Session::flash('success', 'O WhatsApp já estava conectado; o envio por WhatsApp foi ativado.');
                $this->redirect('/configuracoes');
            }

            $qrCode = $evolution->getQrCode();
            Session::set('evolution_qr', $qrCode['base64']);
            Session::set('evolution_pairing_code', $qrCode['code']);
            Session::flash('success', 'QR code atualizado. Escaneie-o com o WhatsApp para conectar.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Não foi possível gerar o QR code: ' . $e->getMessage());
        }

        $this->redirect('/configuracoes');
    }

    public function confirmEvolutionConnection(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/configuracoes');
        }

        if (!$this->validateCsrfToken($this->getPost('csrf_token'))) {
            Session::flash('error', 'Token inválido');
            $this->redirect('/configuracoes');
        }

        try {
            $instance = $this->getEvolutionInstanceName($this->configuracao->get());
            $evolution = $this->getLocalEvolutionApi($instance);
            $evolution->checkConnection();
            $this->configuracao->update([
                'evolution_enabled' => 1,
                'evolution_url' => 'http://127.0.0.1:8080',
                'evolution_instance' => $instance,
                'evolution_api_key' => $this->getLocalEvolutionApiKey()
            ]);
            Session::remove('evolution_qr');
            Session::remove('evolution_pairing_code');
            Session::flash('success', 'WhatsApp conectado e envio por WhatsApp ativado.');
        } catch (\Throwable $e) {
            Session::flash('error', 'O WhatsApp ainda não está conectado: ' . $e->getMessage());
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

    private function getEvolutionInstanceName(?array $config): string
    {
        $instance = trim((string) $this->getPost('evolution_instance', $config['evolution_instance'] ?? 'nps-indmotos'));
        if (!preg_match('/^[A-Za-z0-9_-]{1,100}$/', $instance)) {
            throw new \RuntimeException('Use somente letras, números, hífen ou sublinhado no nome da instância.');
        }

        return $instance;
    }

    private function getLocalEvolutionApi(string $instance): \App\Services\EvolutionApi
    {
        return new \App\Services\EvolutionApi(
            'http://127.0.0.1:8080',
            $this->getLocalEvolutionApiKey(),
            $instance
        );
    }

    private function getLocalEvolutionApiKey(): string
    {
        $envPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
        if (!is_file($envPath) || !is_readable($envPath)) {
            throw new \RuntimeException('Arquivo .env não encontrado. Execute docker\\setup.ps1 primeiro.');
        }

        $environment = parse_ini_file($envPath, false, INI_SCANNER_RAW);
        $apiKey = is_array($environment) ? trim((string) ($environment['AUTHENTICATION_API_KEY'] ?? '')) : '';
        if ($apiKey === '' || str_starts_with($apiKey, 'replace-with-')) {
            throw new \RuntimeException('A chave da Evolution API não está configurada no arquivo .env.');
        }

        return $apiKey;
    }

    private function saveLocalEvolutionSettings(string $instance): void
    {
        $config = $this->configuracao->get();
        $sameInstance = ($config['evolution_instance'] ?? null) === $instance;
        $this->configuracao->update([
            'evolution_enabled' => $sameInstance ? (int) ($config['evolution_enabled'] ?? 0) : 0,
            'evolution_url' => 'http://127.0.0.1:8080',
            'evolution_instance' => $instance,
            'evolution_api_key' => $this->getLocalEvolutionApiKey()
        ]);
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
