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
            $table->foreignId('etudiant_id')->comment('Étudiant concerné')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('examen_id')->comment('Examen concerné')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('copie_id')->comment('Copie associée')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('manchette_id')->comment('Manchette associée')
                  ->constrained()
                  ->onDelete('cascade');
            $table->decimal('note', 5, 2)->comment('Note finale');
            $table->timestamp('date_fusion')->useCurrent();
            $table->timestamps();

            // Un étudiant ne peut avoir qu'un seul résultat par examen
            $table->unique(['etudiant_id', 'examen_id'], 'unique_resultat_etudiant');

            // Une copie et une manchette ne peuvent être associées qu'à un seul résultat
            $table->unique('copie_id', 'unique_resultat_copie');
            $table->unique('manchette_id', 'unique_resultat_manchette');
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
