<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Chat;

class ChamadosAdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'status' => $this->status->status,
            'prioridade' => $this->prioridade,
            'categoria' => $this->categoria->categoria,
            'solicitante' => $this->solicitante->name,
            'tecnico' => $this->tecnico->name ?? 'Não designado',
            'tecnico_secundario' => $this->tecnico_secundario->name ?? null,
            'prioridade' => $this->prioridade,
            'dt_abertura' => $this->formatarData($this->created_at),
            'dt_fechamento' => $this->formatarData($this->dt_fechamento),
            'updated_at' => $this->formatarData($this->updated_at),
            'qtd_mensagens_nao_lidas' => $this->qtdMensagensNaoLidas(),
            'anexo' => $this->anexos ?? null,
        ];
    }

/*************  ✨ Windsurf Command ⭐  *************/
/*******  479e6eed-dd3b-493a-a6f6-9f9979c0d901  *******/
    private function formatarData($data)
    {
        return $data ? $data->format('d/m/Y H:i:s') : null;
    }

    private function qtdMensagensNaoLidas()
    {
        return Chat::query()
            ->where('chamado_id', $this->id)
            ->where('usuario_id', '!=', $this->tecnico_id)
            ->where('lida', false)
            ->count();
    }
}
