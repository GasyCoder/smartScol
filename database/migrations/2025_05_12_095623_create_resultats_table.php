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
        Schema::create('resultats', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('etudiant_id')->comment('Étudiant concerné');
            $table->unsignedBigInteger('examen_id')->comment('Examen concerné');
            $table->unsignedBigInteger('code_anonymat_id')->comment('Code d\'anonymat utilisé');
            $table->decimal('note', 5, 2)->comment('Note finale');
            $table->unsignedBigInteger('genere_par')->comment('Utilisateur ayant généré le résultat');
            $table->timestamp('date_generation')->useCurrent();
            $table->enum('statut', ['provisoire', 'valide', 'publie'])->default('provisoire');
            $table->timestamps();

            // Contraintes étrangères
            $table->foreign('etudiant_id')->references('id')->on('etudiants')->onDelete('cascade');
            $table->foreign('examen_id')->references('id')->on('examens')->onDelete('cascade');
            $table->foreign('code_anonymat_id')->references('id')->on('codes_anonymat')->onDelete('cascade');
            $table->foreign('genere_par')->references('id')->on('users')->onDelete('cascade');

            // Contrainte d'unicité
            $table->unique(['etudiant_id', 'examen_id'], 'unique_resultat_etudiant');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultats');
    }
};
