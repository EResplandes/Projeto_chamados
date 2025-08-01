<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chamado_id');
            $table->unsignedBigInteger('usuario_id'); // ID do usuário que enviou a mensagem
            $table->text('mensagem'); // Conteúdo da mensagem
            $table->timestamp('enviado_em')->useCurrent(); // Data e hora em que a mensagem foi enviada
            $table->boolean('lida')->default(false); // Indica se a mensagem foi lida ou não
            $table->foreign('chamado_id')->references('id')->on('chamados')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat');
    }
};
