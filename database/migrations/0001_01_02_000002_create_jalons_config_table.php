<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jalons_config', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('ordre')->unique();
            $table->string('nom', 80);
            $table->text('condition_passage')->nullable();
            $table->boolean('visible_client')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jalons_config');
    }
};
