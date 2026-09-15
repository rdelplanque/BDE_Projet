<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participations', function (Blueprint $table) {$table->id();
            $table->foreignId('evenement_id')
                ->constrained('evenements')
                ->cascadeOnDelete();
            $table->foreignId('etudiant_id')
                ->constrained('etudiants')
                ->cascadeOnDelete();
            $table->integer('gain_total')
                ->default(0);
            $table->timestamps();

            $table->unique(['evenement_id', 'etudiant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participations');
    }
};
