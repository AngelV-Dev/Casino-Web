<?php

// database/migrations/..._add_game_metrics_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('games_played')->default(0)->after('balance'); // Añade la columna 'games_played'
            $table->unsignedSmallInteger('consecutive_wins')->default(0)->after('games_played'); // Añade la columna 'consecutive_wins'
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('consecutive_wins');
            $table->dropColumn('games_played');
        });
    }
};