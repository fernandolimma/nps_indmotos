<?php

namespace App\Core;

abstract class Controller
{
    protected function redirect(string $path): void
    {
        header('Location: ' . Router::baseUrl($path));
        exit;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function getPost(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    protected function getGet(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    protected function requireAuth(): void
    {
        if (!Session::has('usuario_id')) {
            $this->redirect('/login');
        }
    }

    protected function requireMaster(): void
    {
        $this->requireAuth();

        if (!$this->isMaster()) {
            Session::flash('error', 'Acesso permitido apenas para usuarios MASTER.');
            $this->redirect('/dashboard');
        }
    }

    protected function isMaster(): bool
    {
        return Session::get('usuario_tipo') === 'MASTER';
    }

    protected function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::set('csrf_token', $token);
        return $token;
    }

    protected function validateCsrfToken(?string $token): bool
    {
        return $token && hash_equals(Session::get('csrf_token', ''), $token);
    }
}
