<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('couts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers')->cascadeOnDelete();
            $table->string('libelle');
            $table->enum('categorie', ['transit', 'douane', 'manutention', 'transport', 'divers'])->default('divers');
            $table->decimal('montant', 12, 0);
            $table->date('date');
            $table->string('fournisseur')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('saisi_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['dossier_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couts');
    }
};
