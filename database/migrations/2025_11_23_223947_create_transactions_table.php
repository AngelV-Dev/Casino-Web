<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // 1. TRUCO VITAL: Borrar si existe para evitar el error que te salía
        Schema::dropIfExists('transactions');

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // La columna user_id es obligatoria, se mantiene NOT NULL
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // ✅ CORRECCIÓN: Hacemos wallet_id nullable para solucionar el error 150,
            // ya que a veces MySQL no permite crear una FK NOT NULL de forma inmediata.
            $table->foreignId('wallet_id')->nullable()->constrained('wallets')->cascadeOnDelete();

            $table->enum('type', [
                'deposit',    // depósito
                'withdraw',   // retiro
                'bet',        // apuesta
                'win',        // ganancia
                'bonus'       // bono
            ]);

            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);
            
            $table->string('description')->nullable(); 
            $table->string('reference')->nullable();
            
            $table->string('status')->default('completed'); 

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};