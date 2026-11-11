<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('transitaire_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('regime_id')->constrained('regimes')->restrictOnDelete();

            $table->string('reference', 30)->unique();       // JL-2027-0031
            $table->string('code_suivi', 15)->unique();       // K7M2-9QX4

            $table->string('ligne_maritime')->nullable();     // MSC, Maersk, CMA CGM...
            $table->string('navire')->nullable();
            $table->string('numero_bl', 60)->nullable();
            $table->string('nature_marchandise')->nullable();
            $table->date('date_arrivee_prevue')->nullable();

            $table->unsignedTinyInteger('jalon_actuel')->default(1);
            $table->boolean('brouillon')->default(true);
            $table->decimal('marge_previsionnelle', 12, 0)->default(0);

            $table->date('date_ouverture')->nullable();
            $table->date('date_cloture')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['agence_id', 'jalon_actuel']);
            $table->index(['client_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossiers');
    }
};
