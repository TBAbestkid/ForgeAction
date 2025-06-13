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
        Schema::create('status_personagem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chave_personagem_id')->constrained('chave_personagem')->onDelete('cascade');
            $table->integer('stap_vida');
            $table->integer('stap_mana');
            $table->integer('stap_iniciativa');
            $table->timestamps();
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_personagem');
    }
};
