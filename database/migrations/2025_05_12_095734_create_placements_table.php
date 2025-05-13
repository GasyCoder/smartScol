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
     Schema::create('placements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examen_id');
            $table->unsignedBigInteger('etudiant_id');
            $table->unsignedBigInteger('salle_id');
            $table->string('place', 10)->nullable()->comment('Numéro de place dans la salle');
            $table->boolean('is_present')->nullable()->comment('Présence à l\'examen');
            $table->timestamps();

            $table->foreign('examen_id')->references('id')->on('examens')->onDelete('cascade');
            $table->foreign('etudiant_id')->references('id')->on('etudiants')->onDelete('cascade');
            $table->foreign('salle_id')->references('id')->on('salles')->onDelete('restrict');

            $table->unique(['examen_id', 'etudiant_id']); // Un étudiant ne peut être placé qu'une seule fois par examen
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('placements');
    }
};
