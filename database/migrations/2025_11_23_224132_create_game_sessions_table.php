<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();

            // Dinero
            $table->decimal('bet_amount', 12, 2)->default(0);
            $table->decimal('win_amount', 12, 2)->default(0);

            // --- LAS COLUMNAS QUE FALTABAN ---
            $table->string('result')->default('playing'); // Para que coincida con tu código PHP
            $table->text('game_data')->nullable();        // Para guardar el JSON de la partida

            // Timestamps
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('game_sessions');
    }
};