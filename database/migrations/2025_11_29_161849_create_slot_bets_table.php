<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('slot_bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('bet_amount', 20, 2);
            $table->integer('lines'); // Número de líneas apostadas (1, 3, 5, 9)
            $table->decimal('total_bet', 20, 2); // bet_amount × lines
            $table->json('result'); // Array de símbolos [["🍒","🍋","🍊"],["⭐","💎","🎰"],["💰","7️⃣","🍒"]]
            $table->json('winning_lines'); // Líneas ganadoras [[0,0,0], [1,1,1]]
            $table->decimal('multiplier', 10, 2);
            $table->decimal('payout', 20, 2);
            $table->decimal('profit', 20, 2);
            $table->boolean('is_win');
            $table->string('server_seed');
            $table->string('client_seed');
            $table->integer('nonce');
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('slot_bets');
    }
};