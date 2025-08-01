<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{

    protected $table = 'chat';

    protected $fillable = [
        'chamado_id',
        'usuario_id',
        'mensagem',
        'visualizado',
        'imagem',
        'lida',
        'enviado_em'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function chamado()
    {
        return $this->belongsTo(Chamados::class, 'chamado_id');
    }
}
