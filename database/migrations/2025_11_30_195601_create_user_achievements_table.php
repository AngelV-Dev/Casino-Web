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
        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('achievement_key'); // 'account_created', 'first_deposit', 'win_streak_5', etc
            $table->string('title');
            $table->string('description');
            $table->string('icon'); // nombre del archivo de imagen
            $table->timestamp('unlocked_at');
            $table->timestamps();

            $table->unique(['user_id', 'achievement_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
    }
};
