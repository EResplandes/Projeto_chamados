<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chamado.{chamadoId}', function ($user, $chamadoId) {
    // Liberar para todos (teste)
    return true;

});
