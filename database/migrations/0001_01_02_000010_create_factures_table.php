<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('dossier_id')->nullable()->constrained('dossiers')->nullOnDelete();
            $table->string('numero', 30)->unique(); // FA-2027-0012

            $table->date('date_facture');
            $table->date('date_echeance');
            $table->decimal('sous_total', 12, 0)->default(0);
            $table->decimal('remise', 12, 0)->default(0);
            $table->decimal('tva_taux', 5, 2)->default(0);
            $table->decimal('tva_montant', 12, 0)->default(0);
            $table->decimal('total', 12, 0)->default(0);
            $table->enum('statut', ['impayee', 'partielle', 'payee'])->default('impayee')->index();
            $table->text('notes')->nullable();
            $table->foreignId('emise_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['agence_id', 'date_facture']);
            $table->index(['client_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};
