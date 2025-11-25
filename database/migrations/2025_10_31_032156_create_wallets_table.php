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
        // Aseguramos que la tabla Wallets use un ID compatible (UNSIGNED BIGINT)
        Schema::create('wallets', function (Blueprint $table) {
            $table->id(); // Esto define el ID primario y UNSIGNED BIGINT
            
            // Clave foránea al usuario
            // Es vital que user_id sea también una FK correcta para que la aplicación funcione
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Saldo actual
            $table->decimal('balance', 12, 2)->default(0.00);
            
            $table->string('currency')->default('USD'); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};