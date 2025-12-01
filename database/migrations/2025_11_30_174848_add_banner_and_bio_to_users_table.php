<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Solo agregar si NO existe
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('email');
            }
            
            if (!Schema::hasColumn('users', 'banner')) {
                $table->string('banner')->nullable()->after('avatar');
            }
            
            if (!Schema::hasColumn('users', 'bio')) {
                $table->string('bio', 200)->nullable()->after('banner');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'banner')) {
                $table->dropColumn('banner');
            }
            if (Schema::hasColumn('users', 'bio')) {
                $table->dropColumn('bio');
            }
        });
    }
};