<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evenements_conteneur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conteneur_id')->constrained('conteneurs')->cascadeOnDelete();
            $table->string('type', 60); // arrivee, declaration, bae, sortie, restitution...
            $table->string('libelle');
            $table->text('description')->nullable();
            $table->date('date_evenement');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['conteneur_id', 'date_evenement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evenements_conteneur');
    }
};
