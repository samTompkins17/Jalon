<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences')->cascadeOnDelete();
            $table->foreignId('dossier_id')->nullable()->constrained('dossiers')->cascadeOnDelete();
            $table->foreignId('conteneur_id')->nullable()->constrained('conteneurs')->cascadeOnDelete();

            $table->enum('type', ['franchise', 'document', 'paiement', 'arrivee', 'autre'])->default('franchise');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('cible')->nullable(); // ex. « Conteneur MSCU 4521870 »
            $table->enum('echeance', ['J-2', 'J-1', 'J0'])->nullable();
            $table->enum('gravite', ['basse', 'moyenne', 'haute', 'critique'])->default('moyenne')->index();
            $table->boolean('lue')->default(false)->index();
            $table->timestamp('lue_at')->nullable();
            $table->date('date_alerte');
            $table->enum('statut_envoi', ['en_attente', 'envoyee', 'echec'])->default('en_attente');
            $table->timestamp('envoyee_at')->nullable();
            $table->timestamps();

            // Empêche de générer deux fois la même alerte de franchise (J-2, J-1, J0).
            $table->unique(['conteneur_id', 'echeance']);
            $table->index(['agence_id', 'lue', 'date_alerte']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertes');
    }
};
