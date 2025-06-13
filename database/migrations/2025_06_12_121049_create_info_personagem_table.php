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
        Schema::create('info_personagem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chave_personagem_id')->constrained('chave_personagem')->onDelete('cascade');
            $table->string('nome');
            $table->unsignedBigInteger('raca_id');
            $table->unsignedBigInteger('classe_id');
            $table->integer('idade');
            $table->string('identificacao'); // homem, mulher, indefinido
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('info_personagem');
    }
};
