<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences')->cascadeOnDelete();
            // Le transitaire qui a créé / suit le client (obligatoire).
            $table->foreignId('transitaire_id')->constrained('users')->restrictOnDelete();
            $table->string('code', 30)->unique(); // CLI-2027-001
            $table->string('nom');
            $table->enum('type', ['entreprise', 'particulier'])->default('entreprise');
            $table->string('email')->nullable();
            $table->string('telephone', 40)->nullable();
            $table->string('whatsapp', 40)->nullable();
            $table->string('adresse')->nullable();
            $table->string('ville', 80)->nullable();
            $table->string('pays', 80)->default('Togo');
            $table->string('contact_nom')->nullable();
            $table->string('contact_telephone', 40)->nullable();
            $table->string('contact_email')->nullable();
            $table->text('notes')->nullable();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['agence_id', 'nom']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
