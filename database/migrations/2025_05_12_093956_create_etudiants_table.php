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
      Schema::create('etudiants', function (Blueprint $table) {
            $table->id();
            $table->string('matricule', 20)->unique()->comment('Numéro d\'identification unique');
            $table->string('nom', 50);
            $table->string('prenom', 50)->nullable();
            $table->string('sexe', 1)->nullable()->comment('M ou F');
            $table->string('date_naissance')->nullable()->comment('Date de naissance');
            $table->unsignedBigInteger('niveau_id')->comment('Niveau d\'études actuel');
            $table->unsignedBigInteger('parcours_id')->nullable()->comment('Parcours (uniquement pour PACES/L1)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes(); // Ajout de soft delete pour conserver l'historique

            $table->foreign('niveau_id')->references('id')->on('niveaux')->onDelete('cascade');
            $table->foreign('parcours_id')->references('id')->on('parcours')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etudiants');
    }
};
