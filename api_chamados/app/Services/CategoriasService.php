<?php

namespace App\Services;

use App\Models\Categorias;

class CategoriasService
{

    public function buscaCategorias()
    {
        try {
            return ['status' => 'success', 'categorias' => Categorias::all(), 'http_code' => 200];
        } catch (\Exception $e) {
            return [
                'status' => 'Erro ao buscar categorias: ' . $e->getMessage(),
                'categorias' => null,
                'http_code' => 500
            ];
        }
    }
}
