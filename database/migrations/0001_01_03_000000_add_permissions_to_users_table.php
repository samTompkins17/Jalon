<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permissions fines par collaborateur, en complément du rôle.
 * Un utilisateur sans permissions explicites hérite des droits de son rôle.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('preferences');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }
};
