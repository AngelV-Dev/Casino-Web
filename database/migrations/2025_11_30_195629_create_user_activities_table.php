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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('activity_type'); // 'game_played', 'achievement_unlocked', 'deposit', etc
            $table->string('game_name')->nullable();
            $table->string('description');
            $table->decimal('amount', 10, 2)->nullable(); // para ganancias
            $table->boolean('won')->default(false);
            $table->timestamp('happened_at');
            $table->timestamps();

            $table->index(['user_id', 'happened_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
