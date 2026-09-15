<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etudiants', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 80);
            $table->string('prenom', 80);
            $table->date('date_naissance')->nullable();
            $table->string('email', 191)->unique();
            $table->string('telephone', 20)->nullable();
            $table->foreignId('classe_id')
                ->constrained('classes')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etudiants');
    }
};
