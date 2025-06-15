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
        Schema::create('resultat_final_historiques', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resultat_final_id')->comment('Référence vers le résultat final');
            $table->string('type_action', 50);
            $table->string('statut_precedent', 50)->nullable()->comment('Statut avant l\'action');
            $table->string('statut_nouveau', 50)->nullable()->comment('Nouveau statut après l\'action');
            $table->unsignedBigInteger('user_id')->comment('Utilisateur ayant effectué l\'action');
            $table->text('motif')->nullable()->comment('Motif de l\'action (pour annulation par exemple)');
            $table->string('decision_precedente', 20)->nullable();
            $table->json('donnees_supplementaires')->nullable()->comment('Données supplémentaires selon le type d\'action');
            $table->timestamp('date_action')->useCurrent()->comment('Date et heure de l\'action');
            $table->string('decision_nouvelle', 20)->nullable();
            $table->timestamps();

            // Contraintes étrangères
            $table->foreign('resultat_final_id')->references('id')->on('resultats_finaux')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Index pour optimisation
            $table->index(['resultat_final_id', 'date_action'], 'idx_historique_resultat_date');
            $table->index(['type_action', 'date_action'], 'idx_historique_type_date');
            $table->index(['user_id', 'date_action'], 'idx_historique_user_date');
            $table->index(['type_action', 'resultat_final_id'], 'idx_historique_type_resultat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultat_final_historiques');
    }
};
