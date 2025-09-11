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
        Schema::create('manchettes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examen_id')->comment('Examen concerné');
            $table->unsignedBigInteger('session_exam_id')->nullable()->comment('Référence à la session d\'examen');
            $table->unsignedBigInteger('code_anonymat_id')->comment('Référence au code d\'anonymat');
            $table->unsignedBigInteger('etudiant_id')->comment('Référence à l\'étudiant');
            $table->unsignedBigInteger('saisie_par')->comment('Utilisateur ayant saisi la manchette');
            $table->timestamp('date_saisie')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            // Contraintes de clé étrangère
            $table->foreign('examen_id')->references('id')->on('examens');
            $table->foreign('session_exam_id')->references('id')->on('session_exams')->onDelete('set null');
            $table->foreign('code_anonymat_id')->references('id')->on('codes_anonymat')->onDelete('cascade');
            $table->foreign('etudiant_id')->references('id')->on('etudiants')->onDelete('cascade');
            $table->foreign('saisie_par')->references('id')->on('users');

            // CONTRAINTE D'UNICITÉ EXISTANTE
            $table->unique(['examen_id', 'code_anonymat_id', 'session_exam_id'], 'manchettes_examen_code_session_unique');
            
            // INDEX EXISTANTS
            $table->index(['examen_id', 'code_anonymat_id'], 'manchettes_examen_code_idx');
            $table->index(['examen_id', 'session_exam_id'], 'manchettes_examen_session_idx');
            
            // NOUVEAUX INDEX POUR OPTIMISER LA FUSION
            $table->index(['etudiant_id', 'session_exam_id'], 'idx_manchettes_etudiant_session');
            $table->index(['session_exam_id', 'deleted_at'], 'idx_manchettes_session_active');
            $table->index(['code_anonymat_id', 'etudiant_id'], 'idx_manchettes_code_etudiant');
            $table->index(['examen_id', 'session_exam_id', 'deleted_at'], 'idx_manchettes_fusion_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manchettes');
    }
};
