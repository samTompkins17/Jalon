<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('alerte_id')->nullable()->constrained('alertes')->cascadeOnDelete();
            $table->enum('canal', ['app', 'email'])->default('app');
            $table->string('titre');
            $table->text('message');
            $table->string('lien')->nullable();
            $table->boolean('lue')->default(false)->index();
            $table->timestamp('lu_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
