<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
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
            'chamado' => $this->chamado,
            'usuario_id' => $this->usuario_id,
            'usuario' => $this->usuario->name,
            'mensagem' => $this->mensagem,
            'imagem' => $this->imagem,
            'lida' => $this->lida,
            'enviado_em' => $this->formatarData(new \DateTime($this->enviado_em)),
        ];
    }

    private function formatarData($data)
    {
        return $data ? $data->format('d/m/Y H:i:s') : null;
    }
}
