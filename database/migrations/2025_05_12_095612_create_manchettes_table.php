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
            $table->foreignId('examen_id')->comment('Examen concerné (détermine l\'EC/matière)')
                  ->constrained()
                  ->onDelete('cascade');
            $table->string('code_anonymat', 10)->comment('Code d\'anonymat complet (Ex: TA1, TA2, SA1)');
            $table->string('matricule_etudiant', 20)->comment('Matricule de l\'étudiant à associer');
            $table->foreignId('saisie_par')->comment('Utilisateur ayant saisi la manchette')
                  ->constrained('users')
                  ->onDelete('restrict');
            $table->timestamp('date_saisie')->useCurrent();
            $table->timestamps();

            // Chaque examen ne peut avoir qu'un seul code d'anonymat spécifique
            $table->unique(['examen_id', 'code_anonymat'], 'unique_manchette_code');

            // Un étudiant ne peut passer qu'une seule fois un examen donné
            $table->unique(['examen_id', 'matricule_etudiant'], 'unique_manchette_etudiant');

            // Index pour la recherche rapide par code d'anonymat
            $table->index('code_anonymat', 'idx_manchette_code');

            // Index pour la recherche rapide par matricule
            $table->index('matricule_etudiant', 'idx_manchette_matricule');
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
