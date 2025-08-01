<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anexos extends Model
{
    protected $table = 'anexos';

    protected $fillable = [
        'chamado_id',
        'caminho',
    ];

    public function chamado()
    {
        return $this->belongsTo(Chamados::class, 'chamado_id');
    }
}
