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
        Schema::table('demandes_agence', function (Blueprint $table) {
            $table->text('notes')->nullable();
            $table->timestamp('dernier_contact_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes_agence', function (Blueprint $table) {
            $table->dropColumn(['notes', 'dernier_contact_at']);
        });
    }
};
