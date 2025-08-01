<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CategoriasService;

class CategoriasController extends Controller
{

    protected $categoriasService;

    public function __construct(CategoriasService $categoriasService)
    {
        $this->categoriasService = $categoriasService;
    }

    public function buscaCategorias()
    {
        $categorias = $this->categoriasService->buscaCategorias();
        return response()->json([
            'categorias' => $categorias['categorias'],
            'status' => $categorias['status'],
        ], $categorias['http_code'] ?? 500);
    }
}
