<?php

namespace App\Services;

use App\Models\Chat;
use App\Http\Resources\ChatResource;

class ChatService
{
    public function buscaMensagens($id)
    {
        try {
            $mensagens = ChatResource::collection(
                Chat::where('chamado_id', $id)
                    ->orderBy('enviado_em', 'asc')
                    ->get()
            );


            return [
                'mensagems' => $mensagens,
                'status' => 'success',
                'http_code' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'http_code' => 500,
            ];
        }
    }

    public function enviarMensagem($request)
    {
        try {
            $chat = Chat::create([
                'chamado_id' => $request->chamado_id,
                'usuario_id' => $request->usuario_id,
                'mensagem' => $request->mensagem,
                'lida' => false,
                'enviado_em' => now(),
            ]);

            return [
                'mensagem' => $chat,
                'status' => 'success',
                'http_code' => 201
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'http_code' => 500,
            ];
        }
    }

    public function enviarAnexo($request)
    {
        try {
            $chat = Chat::create([
                'chamado_id' => $request->chamado_id,
                'usuario_id' => $request->usuario_id,
                'mensagem' => 'Imagem',
                'lida' => false,
                'imagem' => $request->file('anexo')->store('anexos', 'public'),
                'enviado_em' => now(),
            ]);

            return [
                'mensagem' => $chat,
                'status' => 'success',
                'http_code' => 201
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'http_code' => 500,
            ];
        }
    }
}
