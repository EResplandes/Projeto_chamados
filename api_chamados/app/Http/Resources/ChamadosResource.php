<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChamadosResource extends JsonResource
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
            'prioridade' => $this->prioridade,
            'dt_abertura' => $this->formatarData($this->created_at),
            'dt_fechamento' => $this->formatarData($this->dt_fechamento),
            'updated_at' => $this->formatarData($this->updated_at),
        ];
    }

    private function formatarData($data)
    {
        return $data ? $data->format('d/m/Y H:i:s') : null;
    }
}
