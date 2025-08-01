<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatService;

class ChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function buscaMensagens($id)
    {
        $mensagens = $this->chatService->buscaMensagens($id);

        return response()->json([
            'mensagens' => $mensagens['mensagems'] ?? [],
            'status' => $mensagens['status'] ?? 'error',
        ], $mensagens['http_code'] ?? 400);
    }

    public function enviarMensagem(Request $request)
    {
        $response = $this->chatService->enviarMensagem($request);

        return response()->json([
            'mensagem' => $response['mensagem'] ?? null,
            'status' => $response['status'] ?? 'error',
        ], $response['http_code'] ?? 400);
    }

    public function enviarAnexo(Request $request)
    {
        $response = $this->chatService->enviarAnexo($request);

        return response()->json([
            'mensagem' => $response['mensagem'] ?? null,
            'status' => $response['status'] ?? 'error',
        ], $response['http_code'] ?? 400);
    }
}
