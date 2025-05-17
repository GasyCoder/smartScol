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
       Schema::create('deliberations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('niveau_id')->comment('Niveau concerné');
            $table->unsignedBigInteger('session_id')->comment('Session d\'examen');
            $table->unsignedBigInteger('annee_universitaire_id')->comment('Année universitaire');
            $table->dateTime('date_deliberation');
            $table->unsignedBigInteger('president_jury')->comment('Enseignant président du jury');
            $table->timestamps();

            $table->foreign('niveau_id')->references('id')->on('niveaux')->onDelete('cascade');
            $table->foreign('session_id')->references('id')->on('session_exams')->onDelete('cascade');
            $table->foreign('annee_universitaire_id')->references('id')->on('annees_universitaires')->onDelete('cascade');
            $table->foreign('president_jury')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['niveau_id', 'session_id', 'annee_universitaire_id']); // Une délibération unique par niveau/session/année
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliberations');
    }
};
