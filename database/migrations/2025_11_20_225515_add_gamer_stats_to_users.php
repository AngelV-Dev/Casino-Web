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
    Schema::table('users', function (Blueprint $table) {
        $table->integer('level')->default(1);
        $table->integer('xp')->default(0);
        $table->integer('xp_next')->default(100); // Cuánto falta para subir de nivel
        $table->text('bio')->nullable(); // Para la frase tipo Steam
    });
    }

    /**
     * Reverse the migrations.
     */
public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'level')) {
                $table->dropColumn('level');
            }
            if (Schema::hasColumn('users', 'xp')) {
                $table->dropColumn('xp');
            }
            if (Schema::hasColumn('users', 'xp_next')) {
                $table->dropColumn('xp_next');
            }
            if (Schema::hasColumn('users', 'bio')) {
                $table->dropColumn('bio');
            }
        });
    }
};
