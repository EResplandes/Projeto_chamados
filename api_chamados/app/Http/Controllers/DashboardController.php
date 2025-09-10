<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DashboardService;

class DashboardController extends Controller
{

    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function indicadoresGerais()
    {
        $query = $this->dashboardService->indicadoresGerais();
        return response()->json([
            'status' => $query['status'] ?? null,
            'status_servicos' => $query['status_servicos'] ?? null,
            'indicadores' => $query['indicadores'] ?? null,
            'chamados' => $query['chamados'] ?? null,
            'chamados_categorias' => $query['chamados_categorias'] ?? null,
            'mensagens_nao_lidas' => $query['mensagens_nao_lidas'] ?? null,
            'error' => $query['error'] ?? null,
        ], $query['http_code'] ?? 500);
    }
}
