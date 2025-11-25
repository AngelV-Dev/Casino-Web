<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crash_games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            $table->decimal('bet_amount', 10, 2);
            $table->decimal('crash_point', 10, 2);
            $table->decimal('cashout_at', 10, 2)->nullable();
            $table->decimal('profit', 10, 2)->nullable();

            $table->boolean('won')->default(false);

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crash_games');
    }
};
