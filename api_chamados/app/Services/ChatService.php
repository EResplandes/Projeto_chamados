<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\User;
use App\Http\Resources\ChatResource;
use App\Models\Chamados;
use App\Mail\Mensagem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

            $recuperandoSolicitanteId = Chamados::where('id', $request->chamado_id)->pluck('solicitante_id')->first();

            $tituloChamado = Chamados::where('id', $request->chamado_id)->pluck('titulo')->first();

            $solicitante = User::where('id', $recuperandoSolicitanteId)->first();

            $dados = [
                'nome' => $solicitante->name,
                'mensagem' => $request->mensagem,
                'titulo' => $tituloChamado
            ];

            Mail::to($solicitante->email)->send(new Mensagem($dados));

            \Log::info('Notificação enviada para ' . $solicitante->email);

            logger('Notificação enviada para ' . $solicitante->email);

            return [
                'mensagem' => $chat,
                'status' => 'success',
                'http_code' => 201
            ];
        } catch (\Exception $e) {
            dd($e);
            return [
                'status' => $e,
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
