<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chamados extends Model
{

    protected $table = 'chamados';

    protected $fillable = [
        'titulo',
        'descricao',
        'prioridade',
        'tecnico_id',
        'tecnico_secundario_id',
        'solicitante_id',
        'categoria_id',
        'status_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function tecnico_secundario()
    {
        return $this->belongsTo(User::class, 'tecnico_secundario_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categorias::class, 'categoria_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function anexos()
    {
        return $this->hasMany(Anexos::class, 'chamado_id');
    }
}
