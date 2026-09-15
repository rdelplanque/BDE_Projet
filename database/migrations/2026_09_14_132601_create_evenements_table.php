<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evenements', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 80);
            $table->dateTime('date_evenement');
            $table->text('detail')->nullable();
            $table->decimal('prix', 8, 2)->default(0.00);
            $table->integer('nombre_place')->nullable();
            $table->integer('gain_base')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evenements');
    }
};
