<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AutenticacaoService
{
    /**
     * Attempt to authenticate a user with the given credentials.
     *
     * @param array $credentials
     * @return User|null
     */

    public function login($request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return [
                'token' => null,
                'user' => null,
                'http_code' => 401,
            ];
        }

        $token = JWTAuth::attempt($credentials);

        return [
            'token' => $token,
            'user' => auth()->user(),
            'http_code' => 200
        ];
    }

    public function logout($id)
    {
        auth()->logout();
        return ['message' => 'Logout realizado com sucesso'];
    }

    public function me($id)
    {
        return [
            'user' => User::where('id', $id)->firstOrFail(),
            'token' => auth()->tokenById($id)
        ];
    }
}
