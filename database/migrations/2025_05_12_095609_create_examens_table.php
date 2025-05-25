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
        Schema::create('examens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id')->comment('Session à laquelle appartient l\'examen');
            $table->unsignedBigInteger('niveau_id')->comment('Niveau concerné');
            $table->unsignedBigInteger('parcours_id')->nullable()->comment('Parcours concerné (uniquement pour PACES/L1)');
            $table->integer('duree')->comment('Durée en minutes');
            $table->decimal('note_eliminatoire', 5, 2)->nullable()->comment('Note éliminatoire pour les concours');
            $table->timestamps();
            $table->softDeletes(); // Ajout de soft delete

            $table->foreign('session_id')->references('id')->on('session_exams')->onDelete('restrict');
            $table->foreign('niveau_id')->references('id')->on('niveaux')->onDelete('restrict');
            $table->foreign('parcours_id')->references('id')->on('parcours')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examens');
    }
};
