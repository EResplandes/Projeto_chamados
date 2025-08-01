<?php

namespace App\Http\Controllers;

use App\Http\Requests\AutenticacaoRequest;
use Illuminate\Http\Request;
use App\Services\AutenticacaoService;

class AutenticacaoController extends Controller
{

    protected $autenticacaoService;

    public function __construct(AutenticacaoService $autenticacaoService)
    {
        $this->autenticacaoService = $autenticacaoService;
    }


    public function login(Request $request)
    {
        $retorno = $this->autenticacaoService->login($request);

        return response()->json([
            'token' => $retorno['token'],
            'user' => $retorno['user']
        ], $retorno['http_code'] ?? 500);
    }
}
