<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuariosResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
            'tipo_usuario' => $this->tipo_usuario,
            'ativo' => $this->ativo,
            'created_at' => $this->formatarData($this->created_at),
            'updated_at' => $this->updated_at,
        ];
    }

    private function formatarData($data)
    {
        return $data ? $data->format('d/m/Y H:i:s') : null;
    }
}
