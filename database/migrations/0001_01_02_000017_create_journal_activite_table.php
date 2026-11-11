<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_activite', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dossier_id')->nullable()->constrained('dossiers')->nullOnDelete();
            $table->string('action', 80);
            $table->text('description')->nullable();
            $table->string('sujet_type', 80)->nullable();
            $table->unsignedBigInteger('sujet_id')->nullable();
            $table->timestamps();

            $table->index(['agence_id', 'created_at']);
            $table->index(['sujet_type', 'sujet_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_activite');
    }
};
