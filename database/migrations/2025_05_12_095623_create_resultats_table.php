<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResultatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resultats', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('etudiant_id')->comment('Étudiant concerné');
            $table->unsignedBigInteger('examen_id')->comment('Examen concerné');
            $table->unsignedBigInteger('code_anonymat_id')->comment('Code d\'anonymat utilisé');
            $table->unsignedBigInteger('ec_id');
            $table->decimal('note', 5, 2)->comment('Note finale');
            $table->unsignedBigInteger('genere_par')->comment('Utilisateur ayant généré le résultat');
            $table->timestamp('date_generation')->useCurrent();
            $table->unsignedBigInteger('modifie_par')->nullable();
            $table->timestamp('date_modification')->nullable();
            $table->enum('statut', ['provisoire', 'valide', 'publie'])->default('provisoire');
            $table->text('observation_jury')->nullable()->comment('Observation du jury');
            $table->enum('decision', ['admis', 'ajourne', 'rattrapage'])->nullable();
            $table->unsignedBigInteger('deliberation_id')->nullable();
            $table->timestamps();

            // Contraintes étrangères
            $table->foreign('etudiant_id')->references('id')->on('etudiants')->onDelete('cascade');
            $table->foreign('examen_id')->references('id')->on('examens')->onDelete('cascade');
            $table->foreign('code_anonymat_id')->references('id')->on('codes_anonymat')->onDelete('cascade');
            $table->foreign('ec_id')->references('id')->on('ecs');
            $table->foreign('genere_par')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('modifie_par')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deliberation_id')->references('id')->on('deliberations')->nullOnDelete();

            // Contrainte d'unicité
            $table->unique(['etudiant_id', 'examen_id', 'ec_id'], 'unique_resultat_etudiant');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resultats');
    }
}
