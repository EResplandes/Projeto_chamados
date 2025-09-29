<?php

namespace App\Services;

use App\Models\Chamados;
use App\Http\Resources\ChamadosResource;
use App\Http\Resources\ChamadosAdminResource;
use App\Models\Anexos;
use App\Models\HistoricoChamados;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ChamadosService
{

    public function buscaChamados($id)
    {
        try {
            return [
                'chamados' => ChamadosResource::collection(
                    Chamados::where('solicitante_id', $id)
                        ->orderBy('created_at', 'desc')
                        ->get()
                ),
                'status' => 'Chamado encontrado com sucesso!',
                'http_code' => 200,
            ];
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Erro ao buscar chamados: ' . $e->getMessage(),
                'http_code' => 500,
            ], 500);
        }
    }

    public function indicadoresUsuario($id)
    {
        try {
            $chamados = Chamados::where('solicitante_id', $id)->get();
            $totalChamados = $chamados->count();
            $chamadosAbertos = $chamados->where('status_id', 1)->count();
            $chamadosAndamento = $chamados->where('status_id', 2)->count();
            $chamadosFechados = $chamados->where('status_id', 3)->count();
            return [
                'indicadores' => [
                    'total_chamados' => $totalChamados,
                    'chamados_abertos' => $chamadosAbertos,
                    'chamados_andamento' => $chamadosAndamento,
                    'chamados_fechados' => $chamadosFechados,
                ],
                'status' => 'Indicadores encontrados com sucesso!',
                'http_code' => 200,
            ];
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Erro ao buscar chamados: ' . $e->getMessage(),
                'http_code' => 500,
            ], 500);
        }
    }

    public function abrirChamado($request)
    {
        DB::beginTransaction();

        try {
            // 1ª Passo -> Validar os dados recebidos
            $dados = [
                'titulo' =>  strtoupper($request->titulo),
                'descricao' => strtoupper($request->descricao),
                'status_id' => 1,
                'prioridade' => $request->urgencia,
                'categoria_id' => $request->categoria_id,
                'solicitante_id' => $request->solicitante_id,
            ];

            // 2ª Passo -> Criar o chamado
            $chamado = Chamados::create($dados);

            // 3º Passo -> Gerar histórico do chamado
            $solicitante = User::where('id', $request->solicitante_id)->pluck('name')->first();

            $historico = HistoricoChamados::create([
                'chamado_id' => $chamado->id,
                'log' => 'Chamado registrado com sucesso na data ' . now() . ' por ' . $solicitante . '.',
            ]);

            // 4º Passo -> Registrar anexos
            if ($request->hasFile('anexos')) {
                foreach ($request->file('anexos') as $anexo) {
                    $caminho = $anexo->store('anexos', 'public');
                    $chamado->anexos()->create([
                        'chamado_id' => $chamado->id,
                        'caminho' => $caminho,
                    ]);
                }
            }

            // 5º Passo -> Criar estrutura de diretório com ano/mês
            $ano = date('Y'); // Ano atual
            $mes = date('m'); // Mês atual

            $directory = "/anexos/{$ano}/{$mes}"; // Criando diretório ano/mês

            if ($request->hasFile('anexo')) {
                $anexo = $request->file('anexo')->store($directory, 'public'); // Salvando anexo do ticket

                // 6º Passo -> Salvar caminho no banco
                $anexo = Anexos::create([
                    'chamado_id' => $chamado->id,
                    'caminho' => $anexo,
                ])->save();
            }

            // 7º Passo -> Retornar todos chamados do usuário
            $chamados = ChamadosResource::collection(
                Chamados::where('solicitante_id', $request->solicitante_id)
                    ->orderBy('created_at', 'desc')
                    ->get()
            );

            DB::commit();
            return ['status' => 'Chamado registrado com sucesso!', 'chamados' => $chamados, 'http_code' => 201];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'Erro ao cadastrar chamado: ' . $e->getMessage(), 'http_code' => 500];
        }
    }

    public function buscarChamadosAdmin($id)
    {
        try {
            $novosChamados = ChamadosAdminResource::collection(
                Chamados::where('status_id', '!=', '3')->orderBy('created_at', 'desc')
                    ->get()
            );

            $meusChamados = ChamadosAdminResource::collection(
                Chamados::where(function ($query) use ($id) {
                    $query->where('tecnico_id', $id)
                        ->orWhere('tecnico_secundario_id', $id);
                })
                    ->orderBy('created_at', 'desc')
                    ->get()
            );

            return [
                'novos_chamados' => $novosChamados,
                'meus_chamados' => $meusChamados,
                'status' => 'Chamado encontrado com sucesso!',
                'http_code' => 200,
            ];
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Erro ao buscar chamados: ' . $e->getMessage(),
                'http_code' => 500,
            ], 500);
        }
    }

    public function indicadoresAdmin($id)
    {
        try {
            $novosChamados = Chamados::where('status_id', 1)->count();
            $meusChamados = Chamados::where('tecnico_id', $id)->count();
            $emAndamento = Chamados::where('status_id', 2)->count();
            $resolvidos = Chamados::where('status_id', 3)->count();

            $indicadores = [
                'novos_chamados' => $novosChamados,
                'meus_chamados' => $meusChamados,
                'em_andamento' => $emAndamento,
                'resolvidos' => $resolvidos,
            ];

            return [
                'indicadores' => $indicadores,
                'status' => 'Indicadores encontrados com sucesso!',
                'http_code' => 200,
            ];
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Erro ao buscar indicadores: ' . $e->getMessage(),
                'http_code' => 500,
            ], 500);
        }
    }

    public function assumeChamado($idChamado, $idUsuario)
    {
        try {
            $verificaSeJaTemTecnico = Chamados::where('id', $idChamado)->pluck('tecnico_id')->first();

            if ($verificaSeJaTemTecnico) {
                Chamados::where('id', $idChamado)
                    ->update(
                        [
                            'tecnico_secundario_id' => $idUsuario,
                            'status_id' => 2
                        ]
                    ); // Altera o status para "Em Andamento"
            } else {
                Chamados::where('id', $idChamado)
                    ->update(
                        [
                            'tecnico_id' => $idUsuario,
                            'status_id' => 2
                        ]
                    ); // Altera o status para "Em Andamento"

            }

            return [
                'status' => 'Chamado assumido com sucesso!',
                'http_code' => 200,
            ];
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Erro ao assumir chamado: ' . $e->getMessage(),
                'http_code' => 500,
            ], 500);
        }
    }

    public function alteraStatusChamado($idChamado, $idStatus)
    {
        try {
            $chamado = Chamados::findOrFail($idChamado);
            $chamado->status_id = $idStatus;
            $chamado->save();

            return [
                'status' => 'Status do chamado alterado com sucesso!',
                'http_code' => 200,
            ];
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Erro ao alterar status do chamado: ' . $e->getMessage(),
                'http_code' => 500,
            ], 500);
        }
    }

    public function alteraTecnicoChamado($idChamado, $idTecnico)
    {
        try {

            $verificaSeJaTemTecnico = Chamados::where('id', $idChamado)->pluck('tecnico_id')->first();

            if ($verificaSeJaTemTecnico) {
                $chamado = Chamados::findOrFail($idChamado);
                $chamado->tecnico_secundario_id = $idTecnico;
                $chamado->save();
            } else {
                $chamado = Chamados::findOrFail($idChamado);
                $chamado->tecnico_id = $idTecnico;
                $chamado->save();
            }

            return [
                'status' => 'Tecnico do chamado alterado com sucesso!',
                'http_code' => 200,
            ];
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Erro ao alterar tecnico do chamado: ' . $e->getMessage(),
                'http_code' => 500,
            ], 500);
        }
    }

    public function buscaAnexo($idChamados)
    {
        try {
            $anexos = Anexos::where('chamado_id', $idChamados)->pluck('caminho')->first();

            return [
                'anexos' => $anexos,
                'status' => 'Anexos encontrados com sucesso!',
                'http_code' => 200,
            ];
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Erro ao buscar anexos: ' . $e->getMessage(),
                'http_code' => 500,
            ], 500);
        }
    }
}
