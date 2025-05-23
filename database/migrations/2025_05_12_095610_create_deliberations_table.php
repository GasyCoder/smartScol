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
        Schema::create('deliberations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('niveau_id')->comment('Niveau concerné');
            $table->unsignedBigInteger('session_id')->comment('Session d\'examen');
            $table->unsignedBigInteger('annee_universitaire_id')->comment('Année universitaire');
            $table->dateTime('date_deliberation');

            // Statut amélioré
            $table->enum('statut', ['programmee', 'en_cours', 'terminee', 'validee', 'annulee'])
                ->default('programmee')
                ->comment('Statut de la délibération');

            // Critères de délibération et règles automatiques
            $table->decimal('seuil_admission', 5, 2)->default(10.00)
                ->comment('Moyenne minimale pour admission automatique');
            $table->decimal('seuil_rachat', 5, 2)->default(9.75)
                ->comment('Moyenne minimale pour rachat possible');
            $table->integer('pourcentage_ue_requises')->default(80)
                ->comment('Pourcentage d\'UE à valider pour admission');
            $table->boolean('appliquer_regles_auto')->default(true)
                ->comment('Appliquer automatiquement les règles aux étudiants');

            // Informations générales
            $table->text('observations')->nullable()
                ->comment('Observations du jury');
            $table->text('decisions_speciales')->nullable()
                ->comment('Décisions spéciales prises pendant la délibération');

            // Statistiques (inchangées)
            $table->integer('nombre_admis')->default(0)
                ->comment('Nombre d\'étudiants admis');
            $table->integer('nombre_ajournes')->default(0)
                ->comment('Nombre d\'étudiants ajournés');
            $table->integer('nombre_exclus')->default(0)
                ->comment('Nombre d\'étudiants exclus');
            $table->integer('nombre_rachats')->default(0)
                ->comment('Nombre d\'étudiants rachetés');

            // Traçabilité
            $table->timestamp('date_finalisation')->nullable()
                ->comment('Date de finalisation des décisions');
            $table->timestamp('date_publication')->nullable()
                ->comment('Date de publication des résultats');
            $table->unsignedBigInteger('finalise_par')->nullable()
                ->comment('Utilisateur ayant finalisé la délibération');

            $table->timestamps();

            // Clés étrangères (inchangées)
            $table->foreign('niveau_id')->references('id')->on('niveaux')->onDelete('cascade');
            $table->foreign('session_id')->references('id')->on('session_exams')->onDelete('cascade');
            $table->foreign('annee_universitaire_id')->references('id')->on('annees_universitaires')->onDelete('cascade');
            $table->foreign('finalise_par')->references('id')->on('users')->onDelete('set null');

            // Contrainte d'unicité (inchangée)
            $table->unique(['niveau_id', 'session_id', 'annee_universitaire_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliberations');
    }
};
