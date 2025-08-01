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
        Schema::create('chamados', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descricao');
            $table->unsignedBigInteger('solicitante_id');
            $table->unsignedBigInteger('tecnico_id')->nullable(); // Técnico responsável, pode ser nulo se ainda não foi atribuído
            $table->unsignedBigInteger('categoria_id');
            $table->unsignedBigInteger('status_id')->default(1); // 1 = Aberto, 2 = Em Andamento, 3 = Fechado
            $table->timestamp('dt_fechamento')->nullable();
            $table->string('prioridade')->default('Média'); // Pode ser 'baixa', 'normal', 'alta', 'critica'
            $table->timestamps();

            $table->foreign('tecnico_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('solicitante_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('status')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chamados');
    }
};
