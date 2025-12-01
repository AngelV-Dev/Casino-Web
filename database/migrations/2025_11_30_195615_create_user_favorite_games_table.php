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
        Schema::create('user_favorite_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('game_key'); // 'high_flyer', 'crash', 'mines', etc
            $table->string('game_name');
            $table->string('game_image'); // ruta de la imagen
            $table->integer('hours_played')->default(0);
            $table->integer('games_played')->default(0);
            $table->integer('games_won')->default(0);
            $table->timestamps();

            $table->unique('user_id'); // Solo un juego favorito por usuario
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_favorite_games');
    }
};
