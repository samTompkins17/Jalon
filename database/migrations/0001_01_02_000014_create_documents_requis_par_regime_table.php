<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents_requis_par_regime', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regime_id')->constrained('regimes')->cascadeOnDelete();
            $table->string('libelle', 120);
            $table->boolean('obligatoire')->default(true);
            $table->unsignedTinyInteger('ordre')->default(0);
            $table->timestamps();

            $table->unique(['regime_id', 'libelle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents_requis_par_regime');
    }
};
