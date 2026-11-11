<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Les agences sont créées AVANT les utilisateurs : users.agence_id dépend d'elles.
 * Aucune clé étrangère vers users n'est déclarée ici (l'administrateur est ajouté ensuite).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agences', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('telephone', 40)->nullable();
            $table->string('adresse')->nullable();
            $table->string('ville', 80)->default('Lomé');
            $table->string('pays', 80)->default('Togo');
            $table->string('logo_path')->nullable();
            $table->string('numero_agrement', 80)->nullable();
            $table->enum('type', ['solo', 'agence'])->default('solo');
            $table->string('devise', 10)->default('FCFA');

            // Paramètres de numérotation (modifiables dans Paramètres > Facturation)
            $table->string('dossier_prefixe', 10)->default('JL');
            $table->smallInteger('dossier_annee')->default(2027);
            $table->unsignedInteger('dossier_increment')->default(1);

            $table->string('facture_prefixe', 10)->default('FA');
            $table->smallInteger('facture_annee')->default(2027);
            $table->unsignedInteger('facture_increment')->default(1);

            $table->string('client_prefixe', 10)->default('CLI');
            $table->smallInteger('client_annee')->default(2027);
            $table->unsignedInteger('client_increment')->default(1);

            $table->decimal('tva_taux', 5, 2)->default(18);
            $table->smallInteger('delai_franchise_defaut')->default(4);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agences');
    }
};
