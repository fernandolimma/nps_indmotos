<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\View;
use App\Models\ClienteOS;
use App\Models\Resposta;

class RelatoriosController extends Controller
{
    private ClienteOS $clienteOS;
    private Resposta $resposta;

    public function __construct()
    {
        $this->requireAuth();
        $this->clienteOS = new ClienteOS();
        $this->resposta = new Resposta();
    }

    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $dataInicial = $_GET['data_inicial'] ?? null;
        $dataFinal = $_GET['data_final'] ?? null;

        $dados = $this->clienteOS->getRespondidasPaginated($page, 20, $dataInicial ?: null, $dataFinal ?: null);
        
        View::render('relatorios/index', $dados);
    }

    public function detalhe(int $id): void
    {
        $cliente = $this->clienteOS->findById($id);

        if ($cliente === null) {
            Session::flash('error', 'Cliente não encontrado para o ID informado.');
            $this->redirect('/relatorios');
        }

        $respostas = $this->resposta->getByClienteOS($id);

        if ($respostas === []) {
            Session::flash('error', 'Nenhuma resposta foi encontrada para este cliente.');
            $this->redirect('/relatorios');
        }

        View::render('relatorios/detalhe', [
            'cliente' => $cliente,
            'respostas' => $respostas
        ]);
    }

    public function pendentes(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $dataInicial = $_GET['data_inicial'] ?? null;
        $dataFinal = $_GET['data_final'] ?? null;
        $dados = $this->clienteOS->getPendentesRespostaPaginated($page, 20, $dataInicial ?: null, $dataFinal ?: null);

        View::render('relatorios/pendentes', $dados);
    }

    public function erros(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $dataInicial = $_GET['data_inicial'] ?? null;
        $dataFinal = $_GET['data_final'] ?? null;
        $dados = $this->clienteOS->getEmailsComErroPaginated($page, 20, $dataInicial ?: null, $dataFinal ?: null);

        View::render('relatorios/erros', $dados);
    }
}
