<?php

namespace App\Services;

use App\Models\User;
use App\Http\Resources\UsuariosResource;

class UsuariosService
{

    public function buscaUsuarios()
    {
        return [
            'usuarios' => UsuariosResource::collection(User::all()),
            'status' => 'success',
            'http_code' => 200
        ];
    }

    public function cadastrarUsuario($request)
    {
        try {

            $senha = bin2hex(random_bytes(8));

            $usuario = User::create(
                [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($senha),
                    'ativo' => 1,
                    'tipo_usuario' => $request->tipo_usuario,
                ]
            );

            return [
                'usuario' => $usuario,
                'senha' => $senha,
                'status' => 'success',
                'http_code' => 201
            ];
        } catch (\Exception $e) {
            return [
                'usuario' => null,
                'status' => 'error',
                'http_code' => 500
            ];
        }
    }

    public function alterarStatusUsuario($idUsuario, $status)
    {
        try {

            $usuario = User::find($idUsuario);
            $usuario->ativo = $status;
            $usuario->save();

            return [
                'usuario' => $usuario,
                'status' => 'success',
                'http_code' => 200
            ];
        } catch (\Exception $e) {
            return [
                'usuario' => null,
                'status' => 'error',
                'http_code' => 500
            ];
        }
    }

    public function resetarSenha($idUsuario)
    {
        try {
            $usuario = User::find($idUsuario);
            $novaSenha = bin2hex(random_bytes(8));
            $usuario->password = bcrypt($novaSenha);
            $usuario->primeiro_acesso = 1;
            $usuario->save();

            return [
                'usuario' => $usuario,
                'senha' => $novaSenha,
                'status' => 'success',
                'http_code' => 200
            ];
        } catch (\Exception $e) {
            return [
                'usuario' => null,
                'senha' => null,
                'status' => 'error',
                'http_code' => 500
            ];
        }
    }

    public function alteraSenha($request)
    {
        try {
            $usuario = User::find($request->id);
            $usuario->password = bcrypt($request->senha);
            $usuario->primeiro_acesso = 0;
            $usuario->save();

            return [
                'usuario' => $usuario,
                'status' => 'success',
                'http_code' => 200
            ];
        } catch (\Exception $e) {
            return [
                'usuario' => null,
                'status' => 'error',
                'http_code' => 500
            ];
        }
    }
}
