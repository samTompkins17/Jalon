<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('est_super_admin')->default(false);
        });

        Schema::create('demandes_agence', function (Blueprint $table) {
            $table->id();
            $table->string('nom_agence', 120);
            $table->string('nom_contact', 120);
            $table->string('email', 150);
            $table->string('telephone', 40);
            $table->string('numero_agrement', 80)->nullable();
            $table->string('statut', 20)->default('en_attente')->index();
            $table->string('motif_refus', 500)->nullable();
            $table->foreignId('traitee_par')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('agence_id')->nullable()->constrained('agences')->nullOnDelete();
            $table->timestamp('traitee_at')->nullable();
            $table->timestamps();

            $table->index(['email', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_agence');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('est_super_admin');
        });
    }
};
