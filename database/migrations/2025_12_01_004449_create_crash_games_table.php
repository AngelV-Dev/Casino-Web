<?php

// En database/migrations/...create_crash_games_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crash_games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // ID del usuario que juega
            $table->decimal('bet_amount', 10, 2); // Monto apostado
            $table->decimal('multiplier', 8, 2)->nullable(); // Multiplicador alcanzado (ej: 2.50)
            $table->decimal('win_amount', 10, 2)->nullable(); // Ganancia (si la hay)
            $table->boolean('cashed_out')->default(false); // Si se retiró antes del crash
            $table->timestamp('crashed_at'); // Momento en que la sesión terminó (crash o retiro)
            $table->timestamps(); // created_at y updated_at
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crash_games');
    }
};