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
        Schema::create('presences_examens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examen_id')->comment('Examen concerné');
            $table->unsignedBigInteger('session_exam_id')->nullable()->comment('Session d\'examen'); // AJOUTÉ nullable()
            $table->unsignedBigInteger('salle_id')->comment('Salle concernée');
            $table->unsignedBigInteger('ec_id')->nullable()->comment('Matière spécifique (optionnel)');
            $table->integer('etudiants_presents')->default(0)->comment('Nombre d\'étudiants présents');
            $table->integer('etudiants_absents')->default(0)->comment('Nombre d\'étudiants absents');
            $table->integer('total_attendu')->nullable()->comment('Total d\'étudiants attendus');
            $table->text('observations')->nullable()->comment('Observations sur la présence');
            $table->unsignedBigInteger('saisie_par')->comment('Utilisateur ayant saisi');
            $table->timestamp('date_saisie')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            // Index et contraintes
            $table->index(['examen_id', 'session_exam_id'], 'presence_examen_session_idx');
            $table->index(['session_exam_id'], 'presence_session_idx');

            // Clés étrangères
            $table->foreign('examen_id')->references('id')->on('examens')->onDelete('cascade');
            $table->foreign('session_exam_id')->references('id')->on('session_exams')->onDelete('set null');
            $table->foreign('salle_id')->references('id')->on('salles')->onDelete('cascade');
            $table->foreign('ec_id')->references('id')->on('ecs')->onDelete('set null'); // CHANGÉ à set null aussi
            $table->foreign('saisie_par')->references('id')->on('users');
        });

        // CONTRAINTE UNIQUE séparée pour éviter les problèmes avec les colonnes nullable
        Schema::table('presences_examens', function (Blueprint $table) {
            $table->unique(['examen_id', 'session_exam_id', 'salle_id', 'ec_id'], 'presence_unique_constraint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presences_examens');
    }
};