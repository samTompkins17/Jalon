<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametres_agence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences')->cascadeOnDelete();
            $table->string('cle', 100);
            $table->json('valeur')->nullable();
            $table->string('groupe', 50)->default('general');
            $table->timestamps();

            $table->unique(['agence_id', 'cle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametres_agence');
    }
};
