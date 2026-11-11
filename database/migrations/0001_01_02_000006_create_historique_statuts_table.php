<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historique_statuts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('jalon');
            $table->unsignedTinyInteger('jalon_precedent')->nullable();
            $table->enum('action', ['creation', 'avance', 'recul', 'modification'])->default('avance');
            $table->text('commentaire')->nullable();
            $table->date('date_passage');
            $table->timestamps();

            $table->index(['dossier_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historique_statuts');
    }
};
