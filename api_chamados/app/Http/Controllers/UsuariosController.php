<?php

namespace App\Http\Controllers;

use App\Http\Requests\CadastroUsuarioRequest;
use Illuminate\Http\Request;
use App\Services\UsuariosService;

class UsuariosController extends Controller
{
    protected $usuariosService;

    public function __construct(UsuariosService $usuariosService)
    {
        $this->usuariosService = $usuariosService;
    }

    public function buscaUsuarios()
    {
        $usuarios = $this->usuariosService->buscaUsuarios();

        return response()->json([
            'usuarios' => $usuarios['usuarios'],
            'status' => $usuarios['status'],

        ], $usuarios['http_code']);
    }

    public function cadastrarUsuario(CadastroUsuarioRequest $request)
    {
        $usuario = $this->usuariosService->cadastrarUsuario($request);

        return response()->json([
            'usuario' => $usuario['usuario'],
            'status' => $usuario['status'],
            'senha' => $usuario['senha'] ?? null,
        ], $usuario['http_code']);
    }

    public function alterarStatusUsuario($idUsuario, $status)
    {
        $usuario = $this->usuariosService->alterarStatusUsuario($idUsuario, $status);

        return response()->json([
            'usuario' => $usuario['usuario'],
            'status' => $usuario['status']
        ], $usuario['http_code']);
    }

    public function resetarSenha($idUsuario)
    {

        $usuario = $this->usuariosService->resetarSenha($idUsuario);

        return response()->json([
            'usuario' => $usuario['usuario'],
            'senha' => $usuario['senha'] ?? null,
            'status' => $usuario['status']
        ], $usuario['http_code']);
    }

    public function alteraSenha(Request $request)
    {
        $usuario = $this->usuariosService->alteraSenha($request);

        return response()->json([
            'usuario' => $usuario['usuario'],
            'status' => $usuario['status']
        ], $usuario['http_code']);
    }
}
