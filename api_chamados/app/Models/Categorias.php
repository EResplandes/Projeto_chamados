<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorias extends Model
{

    protected $table = 'categorias';

    protected $fillable = [
        'categoria',
    ];

    public function chamados()
    {
        return $this->hasMany(Chamados::class, 'categoria_id');
    }
}
