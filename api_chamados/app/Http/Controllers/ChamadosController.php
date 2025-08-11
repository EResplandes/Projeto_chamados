<?php

namespace App\Http\Controllers;

use App\Http\Requests\AbrirChamadoRequest;
use Illuminate\Http\Request;
use App\Services\ChamadosService;

class ChamadosController extends Controller
{

    protected $chamadosService;

    public function __construct(ChamadosService $chamadosService)
    {
        $this->chamadosService = $chamadosService;
    }

    public function buscaChamados($id)
    {
        $chamados = $this->chamadosService->buscaChamados($id);
        return response()->json([
            'chamados' => $chamados['chamados'],
            'status' => $chamados['status'],
        ], $chamados['http_code'] ?? 500);
    }

    public function indicadoresUsuario($id)
    {
        $indicadores = $this->chamadosService->indicadoresUsuario($id);
        return response()->json([
            'indicadores' => $indicadores['indicadores'],
            'status' => $indicadores['status'],
        ], $indicadores['http_code'] ?? 500);
    }
    public function abriChamado(AbrirChamadoRequest $request)
    {
        $chamado = $this->chamadosService->abrirChamado($request);
        return response()->json([
            'status' => $chamado['status'],
            'chamados' => $chamado['chamados'] ?? null,
        ], $chamado['http_code'] ?? 500);
    }

    public function buscarChamadosAdmin($id)
    {
        $chamados = $this->chamadosService->buscarChamadosAdmin($id);
        return response()->json([
            'novos_chamados' => $chamados['novos_chamados'] ?? null,
            'meus_chamados' => $chamados['meus_chamados'] ?? null,
            'status' => $chamados['status'],
        ], $chamados['http_code'] ?? 500);
    }

    public function indicadoresAdmin($id)
    {
        $indicadores = $this->chamadosService->indicadoresAdmin($id);

        return response()->json([
            'indicadores' => $indicadores['indicadores'] ?? null,
            'status' => $indicadores['status'] ?? null,
        ], $indicadores['http_code'] ?? 500);
    }

    public function assumeChamado($idChamado, $idUsuario)
    {
        $response = $this->chamadosService->assumeChamado($idChamado, $idUsuario);
        return response()->json([
            'status' => $response['status'],
        ], $response['http_code'] ?? 500);
    }

    public function alteraStatusChamado($idChamado, $idStatus)
    {
        $response = $this->chamadosService->alteraStatusChamado($idChamado, $idStatus);
        return response()->json([
            'status' => $response['status'],
        ], $response['http_code'] ?? 500);
    }

    public function alteraTecnicoChamado($idChamado, $idTecnico)
    {
        $response = $this->chamadosService->alteraTecnicoChamado($idChamado, $idTecnico);
        return response()->json([
            'status' => $response['status'],
        ], $response['http_code'] ?? 500);
    }
}
