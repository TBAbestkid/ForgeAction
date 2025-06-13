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
       Schema::create('atributo_personagem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chave_personagem_id')->constrained('chave_personagem')->onDelete('cascade');
            $table->tinyInteger('forca');
            $table->tinyInteger('agilidade');
            $table->tinyInteger('inteligencia');
            $table->tinyInteger('sabedoria');
            $table->tinyInteger('destreza');
            $table->tinyInteger('vitalidade');
            $table->tinyInteger('percepcao');
            $table->tinyInteger('carisma');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atributo_personagem');
    }
};
