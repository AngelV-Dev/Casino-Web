<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('friends', function (Blueprint $table) {
            $table->id();

            // Usuario que envía la solicitud
            $table->unsignedBigInteger('user_id');

            // Usuario que la recibe
            $table->unsignedBigInteger('friend_id');

            // Estado (pendiente, aceptado, bloqueado)
            $table->enum('status', ['pending', 'accepted', 'blocked'])->default('pending');

            $table->timestamps();

            // Claves foráneas
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('friend_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('friends');
    }
};

