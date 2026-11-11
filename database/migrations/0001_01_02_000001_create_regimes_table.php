<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regimes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // C100, T1, RX, EN...
            $table->string('nom');
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->unsignedTinyInteger('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regimes');
    }
};
