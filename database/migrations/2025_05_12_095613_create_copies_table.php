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
        Schema::create('copies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examen_id')->comment('Examen concerné');
            $table->unsignedBigInteger('session_exam_id')->nullable()->comment('Référence à la session d\'examen');
            $table->unsignedBigInteger('ec_id')->comment('Élément constitutif concerné');
            $table->unsignedBigInteger('code_anonymat_id')->comment('Référence au code d\'anonymat');
            $table->decimal('note', 5, 2)->comment('Note obtenue');
            $table->unsignedBigInteger('saisie_par')->comment('Utilisateur ayant saisi la note');
             $table->foreignId('modifie_par')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('date_saisie')->useCurrent();
            $table->decimal('note_old', 5, 2)->nullable()->comment('Note corrigée');
            $table->boolean('is_checked')->default(false);
            $table->string('commentaire')->nullable()->comment('Commentaire sur la note');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['examen_id', 'code_anonymat_id', 'session_exam_id'], 'copies_examen_code_session_unique');
            $table->index(['examen_id', 'code_anonymat_id'], 'copies_examen_code_idx');
            // Index pour optimiser les requêtes par session
            $table->index(['examen_id', 'ec_id', 'session_exam_id'], 'copies_examen_ec_session_idx');
            // Contrainte de clé étrangère
            $table->foreign('session_exam_id')->references('id')->on('session_exams')->onDelete('set null');
            $table->index('ec_id', 'copies_ec_idx');
            $table->foreign('examen_id')->references('id')->on('examens');
            $table->foreign('ec_id')->references('id')->on('ecs');
            $table->foreign('code_anonymat_id')->references('id')->on('codes_anonymat')->onDelete('cascade');
            $table->foreign('saisie_par')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('copies');
    }
};