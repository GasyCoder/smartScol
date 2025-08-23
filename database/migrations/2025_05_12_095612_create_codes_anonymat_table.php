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
        Schema::create('codes_anonymat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examen_id')->comment('Examen concerné');
            $table->unsignedBigInteger('session_exam_id')->nullable()->comment('Référence à la session d\'examen');
            $table->unsignedBigInteger('ec_id')->nullable();
            $table->string('code_complet', 20)->comment('Code complet d\'anonymat (Ex: TA1, SA2)');
            $table->integer('sequence')->nullable()->comment('Numéro séquentiel (Ex: 1 dans TA1)');
            $table->timestamps();
            $table->softDeletes();

            // Contraintes de clés étrangères
            $table->foreign('examen_id')->references('id')->on('examens')->onDelete('cascade');
            $table->foreign('session_exam_id')->references('id')->on('session_exams')->onDelete('set null');
            $table->foreign('ec_id')->references('id')->on('ecs')->onDelete('cascade');

            $table->unique(['examen_id', 'ec_id', 'session_exam_id', 'code_complet'], 'codes_examen_ec_session_code_unique');

            // Index pour les recherches fréquentes
            $table->index(['examen_id', 'session_exam_id'], 'codes_examen_session_idx');
            $table->index(['ec_id', 'session_exam_id'], 'codes_ec_session_idx');
            $table->index(['code_complet'], 'codes_complet_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codes_anonymat');
    }
};
