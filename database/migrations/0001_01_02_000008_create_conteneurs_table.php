<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conteneurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers')->cascadeOnDelete();
            $table->string('numero', 20)->unique(); // MSCU 4521870
            $table->string('type', 10)->default('40HC'); // 20DV, 40HC, 40DV, 20RF...
            $table->string('taille', 20)->nullable();     // 20 pieds, 40 pieds
            $table->enum('statut', ['au_port', 'sorti', 'restitue'])->default('au_port')->index();
            $table->date('date_arrivee')->nullable();
            $table->date('date_sortie')->nullable();
            $table->date('date_restitution')->nullable();
            $table->date('fin_franchise')->nullable()->index();
            $table->string('ligne_maritime')->nullable();
            $table->string('numero_plomb', 40)->nullable();
            $table->decimal('poids_kg', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conteneurs');
    }
};
