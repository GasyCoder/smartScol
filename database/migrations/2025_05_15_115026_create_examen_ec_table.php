<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('examen_ec', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salle_id');
            $table->unsignedBigInteger('examen_id');
            $table->unsignedBigInteger('ec_id');
            $table->date('date_specifique')->nullable()->comment('Date spécifique de l\'examen (si applicable)');
            $table->time('heure_specifique')->nullable()->comment('Heure spécifique de l\'examen (si applicable)');
            $table->timestamps();

            $table->unique(['salle_id', 'examen_id', 'ec_id']);
            $table->foreign('salle_id')->references('id')->on('salles')->onDelete('cascade');
            $table->foreign('examen_id')->references('id')->on('examens')->onDelete('cascade');
            $table->foreign('ec_id')->references('id')->on('ecs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examen_ec');
    }
};
