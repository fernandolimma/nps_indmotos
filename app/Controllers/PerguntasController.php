<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\View;
use App\Models\Pergunta;

class PerguntasController extends Controller
{
    private Pergunta $pergunta;

    public function __construct()
    {
        $this->requireAuth();
        $this->pergunta = new Pergunta();
    }

    public function index(): void
    {
        $perguntas = $this->pergunta->getAll();
        View::render('perguntas/index', ['perguntas' => $perguntas]);
    }

    public function create(): void
    {
        View::render('perguntas/form', [
            'pergunta' => null,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/perguntas');
        }

        if (!$this->validateCsrfToken($this->getPost('csrf_token'))) {
            Session::flash('error', 'Token inválido');
            $this->redirect('/perguntas');
        }

        $this->pergunta->create([
            'texto_pergunta' => $this->getPost('texto_pergunta'),
            'tipo' => $this->getPost('tipo'),
            'ativa' => $this->getPost('ativa') ? 1 : 0,
            'ordem' => (int)$this->getPost('ordem', 0)
        ]);

        Session::flash('success', 'Pergunta criada com sucesso!');
        $this->redirect('/perguntas');
    }

    public function edit(int $id): void
    {
        $pergunta = $this->pergunta->findById($id);
        if (!$pergunta) {
            $this->redirect('/perguntas');
        }

        View::render('perguntas/form', [
            'pergunta' => $pergunta,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->isPost()) {
            $this->redirect('/perguntas');
        }

        if (!$this->validateCsrfToken($this->getPost('csrf_token'))) {
            Session::flash('error', 'Token inválido');
            $this->redirect('/perguntas');
        }

        $this->pergunta->update($id, [
            'texto_pergunta' => $this->getPost('texto_pergunta'),
            'tipo' => $this->getPost('tipo'),
            'ativa' => $this->getPost('ativa') ? 1 : 0,
            'ordem' => (int)$this->getPost('ordem', 0)
        ]);

        Session::flash('success', 'Pergunta atualizada com sucesso!');
        $this->redirect('/perguntas');
    }

    public function delete(int $id): void
    {
        $this->pergunta->delete($id);
        Session::flash('success', 'Pergunta removida com sucesso!');
        $this->redirect('/perguntas');
    }
}

