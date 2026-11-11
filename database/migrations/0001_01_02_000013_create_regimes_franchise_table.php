<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regimes_franchise', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regime_id')->constrained('regimes')->cascadeOnDelete();
            $table->string('ligne_maritime', 80);
            $table->unsignedSmallInteger('jours')->default(4);
            $table->timestamps();

            $table->unique(['regime_id', 'ligne_maritime']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regimes_franchise');
    }
};
