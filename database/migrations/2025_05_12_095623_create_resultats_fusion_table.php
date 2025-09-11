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
        Schema::create('resultats_fusion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('etudiant_id')->comment('Étudiant concerné');
            $table->unsignedBigInteger('examen_id')->comment('Examen concerné');
            $table->unsignedBigInteger('session_exam_id')->nullable();
            $table->unsignedBigInteger('code_anonymat_id')->comment('Code d\'anonymat utilisé');
            $table->unsignedBigInteger('ec_id');
            $table->decimal('note', 5, 2)->comment('Note à vérifier');
            $table->unsignedBigInteger('genere_par')->comment('Utilisateur ayant généré le résultat');
            $table->unsignedBigInteger('modifie_par')->nullable();
            $table->unsignedInteger('etape_fusion')->default(0)->comment('Compteur de fusion pour éviter les doublons');
            $table->enum('statut', ['verify_1', 'verify_2', 'verify_3', 'valide', 'annule'])->default('verify_1');
            $table->json('status_history')->nullable();
            $table->timestamp('date_validation')->nullable();
            $table->uuid('operation_id')->nullable();
            $table->timestamps();

            // Contraintes étrangères
            $table->foreign('etudiant_id')->references('id')->on('etudiants')->onDelete('cascade');
            $table->foreign('examen_id')->references('id')->on('examens')->onDelete('cascade');
            $table->foreign('session_exam_id')->references('id')->on('session_exams')->onDelete('set null');
            $table->foreign('code_anonymat_id')->references('id')->on('codes_anonymat')->onDelete('cascade');
            $table->foreign('ec_id')->references('id')->on('ecs');
            $table->foreign('genere_par')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('modifie_par')->references('id')->on('users')->onDelete('set null');

            // CONTRAINTE D'UNICITÉ
            $table->unique(['etudiant_id', 'examen_id', 'ec_id', 'session_exam_id'], 'unique_resultat_fusion_etudiant');
            
            // INDEX EXISTANTS
            $table->index(['examen_id', 'session_exam_id', 'statut'], 'rf_examen_session_statut');
            $table->index(['etudiant_id', 'ec_id', 'session_exam_id'], 'rf_etudiant_ec_session');
            $table->index(['examen_id', 'statut', 'session_exam_id', 'ec_id', 'etudiant_id'], 'idx_resultats_fusion_session');
            $table->index(['etudiant_id', 'statut'], 'idx_fusion_etudiant_statut');
            $table->index(['operation_id'], 'idx_fusion_operation');
            
            // NOUVEAUX INDEX POUR OPTIMISER LA FUSION
            $table->index(['statut', 'etape_fusion'], 'idx_rf_statut_etape_batch');
            $table->index(['code_anonymat_id', 'session_exam_id'], 'idx_rf_code_session_join');
            $table->index(['session_exam_id', 'statut', 'etape_fusion'], 'idx_rf_session_statut_etape');
            $table->index(['examen_id', 'session_exam_id', 'etape_fusion'], 'idx_rf_examen_session_etape');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultats_fusion');
    }
};
