<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Les références métier (CLI-2027-001, JL-2027-0031, FA-2027-0012) sont
 * numérotées agence par agence : l'unicité doit donc porter sur le couple
 * (agence_id, référence). Une clé globale obligeait deux agences au même
 * préfixe à se sauter mutuellement des numéros et exposait à des collisions
 * lors de créations simultanées.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_code_unique');
            $table->unique(['agence_id', 'code'], 'clients_agence_code_unique');
        });

        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropUnique('dossiers_reference_unique');
            $table->unique(['agence_id', 'reference'], 'dossiers_agence_reference_unique');
        });

        Schema::table('factures', function (Blueprint $table) {
            $table->dropUnique('factures_numero_unique');
            $table->unique(['agence_id', 'numero'], 'factures_agence_numero_unique');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_agence_code_unique');
            $table->unique('code', 'clients_code_unique');
        });

        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropUnique('dossiers_agence_reference_unique');
            $table->unique('reference', 'dossiers_reference_unique');
        });

        Schema::table('factures', function (Blueprint $table) {
            $table->dropUnique('factures_agence_numero_unique');
            $table->unique('numero', 'factures_numero_unique');
        });
    }
};
