<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricoChamados extends Model
{

    protected $table = 'historico_chamados';

    protected $fillable = [
        'chamado_id',
        'log'
    ];

    public function chamado()
    {
        return $this->belongsTo(Chamados::class, 'chamado_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
