<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AutenticacaoController;
use App\Http\Controllers\CategoriasController;
use App\Http\Controllers\ChamadosController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UsuariosController;

Route::prefix('autenticacao')->group(function () {
    Route::post('/login', [AutenticacaoController::class, 'login']);
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AutenticacaoController::class, 'logout']);
        Route::get('/me', [AutenticacaoController::class, 'me']);
    });
});

Route::prefix('categorias')->group(function () {
    Route::get('/busca-categorias', [CategoriasController::class, 'buscaCategorias']);
})->middleware('auth:api');

Route::prefix('chamados')->group(function () {
    Route::get('/busca-chamados/{id}', [ChamadosController::class, 'buscaChamados']);
    Route::get('/indicadores-usuario/{id}', [ChamadosController::class, 'indicadoresUsuario']);
    Route::post('/abrir-chamado', [ChamadosController::class, 'abriChamado']);

    Route::prefix('admin')->group(function () {
        Route::get('/busca-chamados/{id}', [ChamadosController::class, 'buscarChamadosAdmin']);
        Route::get('indicadores-admin/{id}', [ChamadosController::class, 'indicadoresAdmin']);
        Route::get('/assume-chamado/{idChamado}/{idUsuario}', [ChamadosController::class, 'assumeChamado']);
        Route::get('/altera-status-chamado/{idChamado}/{idStatus}', [ChamadosController::class, 'alteraStatusChamado']);
    });
})->middleware('auth:api');

Route::prefix('usuarios')->group(function () {
    Route::get('/busca-usuarios', [UsuariosController::class, 'buscaUsuarios']);
    Route::post('/criar-usuario', [UsuariosController::class, 'cadastrarUsuario']);
    Route::get('/alterar-status-usuario/{id}/{status}', [UsuariosController::class, 'alterarStatusUsuario']);
    Route::get('/resetar-senha/{id}', [UsuariosController::class, 'resetarSenha']);
    Route::post('/altera-senha', [UsuariosController::class, 'alteraSenha']);
})->middleware('auth:api');

Route::prefix('chat')->group(function () {
    Route::get('/busca-mensagens/{id}', [ChatController::class, 'buscaMensagens']);
    Route::post('/enviar-mensagem', [ChatController::class, 'enviarMensagem']);
    Route::post('/enviar-anexo', [ChatController::class, 'enviarAnexo']);
})->middleware('auth:api');
