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
            $table->unsignedBigInteger('code_anonymat_id')->comment('Référence au code d\'anonymat');
            $table->unsignedBigInteger('etudiant_id')->comment('Référence à l\'étudiant');
            $table->unsignedBigInteger('saisie_par')->comment('Utilisateur ayant saisi la manchette');
            $table->timestamp('date_saisie')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            // Garder cette contrainte: un code_anonymat ne peut être utilisé que dans une seule manchette
            $table->unique(['examen_id', 'code_anonymat_id']);
            $table->index(['examen_id', 'code_anonymat_id'], 'manchettes_examen_code_idx');
            // SUPPRIMÉ: $table->unique(['examen_id', 'etudiant_id']);
            // Cette contrainte empêche un étudiant d'avoir plusieurs manchettes pour différentes matières
            // d'un même examen, ce qui ne correspond pas à la nouvelle logique

            // Contraintes de clé étrangère (inchangées)
            $table->foreign('examen_id')->references('id')->on('examens');
            $table->foreign('code_anonymat_id')->references('id')->on('codes_anonymat')->onDelete('cascade');
            $table->foreign('etudiant_id')->references('id')->on('etudiants')->onDelete('cascade');
            $table->foreign('saisie_par')->references('id')->on('users');
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
