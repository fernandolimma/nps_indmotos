<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\View;
use App\Models\Configuracao;
use App\Models\Usuario;

class AuthController extends Controller
{
    private Usuario $usuario;
    private Configuracao $configuracao;

    public function __construct()
    {
        $this->usuario = new Usuario();
        $this->configuracao = new Configuracao();
    }

    public function login(): void
    {
        if (Session::has('usuario_id')) {
            $this->redirect('/dashboard');
        }
        $config = $this->configuracao->get();
        View::render('auth/login', [
            'logo_url' => $this->resolveLogoUrl($config)
        ]);
    }

    public function authenticate(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/login');
        }

        $email = $this->getPost('email');
        $senha = $this->getPost('senha');

        $usuario = $this->usuario->findByEmail($email);

        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            Session::regenerate();
            Session::set('usuario_id', $usuario['id']);
            Session::set('usuario_nome', $usuario['nome']);
            Session::set('usuario_tipo', $usuario['tipo_usuario'] ?? 'USUARIO');
            $this->redirect('/dashboard');
        } else {
            Session::flash('error', 'E-mail ou senha inválidos');
            $this->redirect('/login');
        }
    }

    public function logout(): void
    {
        Session::destroy();
        $this->redirect('/login');
    }

    public function setup(): void
    {
        $usuarios = $this->usuario->getAll();
        if (count($usuarios) > 0) {
            $this->redirect('/login');
        }

        if ($this->isPost()) {
            $this->usuario->create([
                'nome' => $this->getPost('nome'),
                'email' => $this->getPost('email'),
                'senha' => $this->getPost('senha'),
                'tipo_usuario' => 'MASTER'
            ]);
            Session::flash('success', 'Usuário criado com sucesso! Faça login.');
            $this->redirect('/login');
        }

        View::render('auth/setup');
    }

    private function resolveLogoUrl(?array $config): ?string
    {
        if (empty($config['logo_path'])) {
            return null;
        }

        return \App\Core\Router::baseUrl('/' . ltrim(str_replace('\\', '/', $config['logo_path']), '/'));
    }
}
