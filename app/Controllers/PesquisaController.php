<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\View;
use App\Models\ClienteOS;
use App\Models\Configuracao;
use App\Models\Pergunta;
use App\Models\Resposta;

class PesquisaController extends Controller
{
    private ClienteOS $clienteOS;
    private Configuracao $configuracao;
    private Pergunta $pergunta;
    private Resposta $resposta;

    public function __construct()
    {
        $this->clienteOS = new ClienteOS();
        $this->configuracao = new Configuracao();
        $this->pergunta = new Pergunta();
        $this->resposta = new Resposta();
    }

    public function index(): void
    {
        $token = $this->getGet('token');
        $config = $this->configuracao->get();
        $logoUrl = $this->resolveLogoUrl($config);
        
        if (!$token) {
            View::render('publica/erro', ['mensagem' => 'Token inválido', 'logo_url' => $logoUrl]);
            return;
        }

        $cliente = $this->clienteOS->findByToken($token);

        if (!$cliente) {
            View::render('publica/erro', ['mensagem' => 'Pesquisa não encontrada', 'logo_url' => $logoUrl]);
            return;
        }

        if ($cliente['status_resposta'] === 'respondido') {
            View::render('publica/erro', ['mensagem' => 'Esta pesquisa já foi respondida', 'logo_url' => $logoUrl]);
            return;
        }

        $hoje = date('Y-m-d');
        if ($hoje > $cliente['data_limite']) {
            View::render('publica/erro', ['mensagem' => 'O prazo para responder esta pesquisa expirou', 'logo_url' => $logoUrl]);
            return;
        }

        $perguntas = $this->pergunta->getAll(true);
        $csrfToken = $this->generateCsrfToken();

        View::render('publica/formulario', [
            'cliente' => $cliente,
            'perguntas' => $perguntas,
            'csrf_token' => $csrfToken,
            'logo_url' => $logoUrl
        ]);
    }

    public function submit(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/');
        }

        $token = $this->getPost('token');
        $config = $this->configuracao->get();
        $logoUrl = $this->resolveLogoUrl($config);
        
        if (!$token || !$this->validateCsrfToken($this->getPost('csrf_token'))) {
            View::render('publica/erro', ['mensagem' => 'Token inválido', 'logo_url' => $logoUrl]);
            return;
        }

        $cliente = $this->clienteOS->findByToken($token);

        if (!$cliente || $cliente['status_resposta'] === 'respondido') {
            View::render('publica/erro', ['mensagem' => 'Pesquisa inválida ou já respondida', 'logo_url' => $logoUrl]);
            return;
        }

        $perguntas = $this->pergunta->getAll(true);
        
        foreach ($perguntas as $pergunta) {
            $dadosResposta = [
                'cliente_os_id' => $cliente['id'],
                'pergunta_id' => $pergunta['id']
            ];

            if ($pergunta['tipo'] === 'escala_0_10') {
                $dadosResposta['nota'] = (int)$this->getPost('pergunta_' . $pergunta['id'], 0);
            } else {
                $dadosResposta['resposta_texto'] = $this->getPost('pergunta_' . $pergunta['id'], '');
            }

            $this->resposta->create($dadosResposta);
        }

        $this->clienteOS->updateStatusResposta($cliente['id'], 'respondido');

        View::render('publica/obrigado', [
            'logo_url' => $logoUrl
        ]);
    }

    private function resolveLogoUrl(?array $config): ?string
    {
        if (empty($config['logo_path'])) {
            return null;
        }

        return \App\Core\Router::baseUrl('/' . ltrim(str_replace('\\', '/', $config['logo_path']), '/'));
    }
}
