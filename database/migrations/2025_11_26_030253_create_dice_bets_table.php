<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dice_bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('bet_amount', 20, 2);
            $table->decimal('target_number', 5, 2); // Número objetivo (0-100)
            $table->string('direction', 10); // 'under' o 'over'
            $table->decimal('result_number', 5, 2); // Resultado del dado
            $table->decimal('multiplier', 10, 2); // Multiplicador
            $table->decimal('payout', 20, 2); // Ganancia total
            $table->decimal('profit', 20, 2); // Ganancia neta
            $table->boolean('is_win'); // Ganó o perdió
            $table->string('server_seed'); // Para provably fair
            $table->string('client_seed'); // Para provably fair
            $table->integer('nonce'); // Para provably fair
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('dice_bets');
    }
};