<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\ClienteOS;

class DashboardController extends Controller
{
    private ClienteOS $clienteOS;

    public function __construct()
    {
        $this->requireAuth();
        $this->clienteOS = new ClienteOS();
    }

    public function index(): void
    {
        $total = $this->clienteOS->countMesAtual();
        $respondidas = $this->clienteOS->countRespondidasMesAtual();
        $pendentes = $this->clienteOS->countPendentes();
        $mediaNpsMesAtual = $this->clienteOS->getMediaNpsMesAtual();
        $mediaNotasDetratorasMesAtual = $this->clienteOS->getMediaNotasDetratorasMesAtual();

        View::render('dashboard/index', [
            'total' => $total,
            'respondidas' => $respondidas,
            'pendentes' => $pendentes,
            'media_nps_mes_atual' => $mediaNpsMesAtual,
            'media_notas_detratoras_mes_atual' => $mediaNotasDetratorasMesAtual
        ]);
    }
}
