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
        Schema::create('resultats_finaux', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('etudiant_id')->comment('Étudiant concerné');
            $table->unsignedBigInteger('examen_id')->comment('Examen concerné');
            $table->unsignedBigInteger('session_exam_id')->nullable();
            $table->unsignedBigInteger('code_anonymat_id')->comment('Code d\'anonymat utilisé');
            $table->unsignedBigInteger('ec_id');
            $table->decimal('note', 5, 2)->comment('Note finale');
            $table->unsignedBigInteger('genere_par')->comment('Utilisateur ayant généré le résultat');
            $table->unsignedBigInteger('modifie_par')->nullable();
            $table->enum('statut', ['en_attente', 'publie', 'annule'])->default('en_attente');
            $table->json('status_history')->nullable();
            $table->text('motif_annulation')->nullable();
            $table->timestamp('date_annulation')->nullable();
            $table->unsignedBigInteger('annule_par')->nullable();
            $table->timestamp('date_reactivation')->nullable();
            $table->unsignedBigInteger('reactive_par')->nullable();
            $table->enum('decision', ['admis', 'rattrapage', 'redoublant', 'exclus'])->nullable();
            $table->timestamp('date_publication')->nullable();
            $table->string('hash_verification', 64)->nullable();
            $table->unsignedBigInteger('deliberation_id')->nullable();
            $table->unsignedBigInteger('fusion_id')->nullable()->comment('ID du résultat fusion source');
            $table->timestamp('date_fusion')->nullable()->comment('Date du transfert depuis fusion');
            $table->timestamps();

            // Contraintes étrangères (identiques)
            $table->foreign('etudiant_id')->references('id')->on('etudiants')->onDelete('cascade');
            $table->foreign('examen_id')->references('id')->on('examens')->onDelete('cascade');
            $table->foreign('session_exam_id')->references('id')->on('session_exams')->onDelete('set null');
            $table->foreign('code_anonymat_id')->references('id')->on('codes_anonymat')->onDelete('cascade');
            $table->foreign('ec_id')->references('id')->on('ecs');
            $table->foreign('genere_par')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('modifie_par')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deliberation_id')->references('id')->on('deliberations')->onDelete('set null');
            $table->foreign('fusion_id')->references('id')->on('resultats_fusion')->onDelete('set null');
            $table->foreign('annule_par')->references('id')->on('users')
                ->onDelete('set null')
                ->name('fk_resultats_finaux_annule_par');
            $table->foreign('reactive_par')->references('id')->on('users')
                ->onDelete('set null')
                ->name('fk_resultats_finaux_reactive_par');

            // CONTRAINTE D'UNICITÉ CORRIGÉE - SEUL CHANGEMENT !
            // AVANT (INCORRECT) :
            // $table->unique(['etudiant_id', 'examen_id', 'ec_id'], 'unique_resultat_final_etudiant');

            // APRÈS (CORRECT) :
            $table->unique(['etudiant_id', 'examen_id', 'ec_id', 'session_exam_id'], 'unique_resultat_final_etudiant');

            // Index pour optimisation (identiques)
            $table->index(['statut', 'date_reactivation'], 'idx_statut_reactivation');
            $table->index(['date_annulation'], 'idx_date_annulation');
            $table->index(['examen_id', 'statut', 'session_exam_id', 'ec_id', 'etudiant_id'], 'idx_resultats_fusion_session');
            $table->index(['etudiant_id', 'statut'], 'idx_final_etudiant_statut');
            $table->index(['deliberation_id'], 'idx_final_deliberation');
            $table->index(['decision'], 'idx_final_decision');
            $table->index(['date_publication'], 'idx_final_date_publication');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultats_finaux');
    }
};
