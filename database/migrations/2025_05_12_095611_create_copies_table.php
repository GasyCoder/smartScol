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
            $table->foreignId('examen_id')->comment('Examen concerné (détermine l\'EC/matière)')
                  ->constrained()
                  ->onDelete('cascade');
            $table->string('code_anonymat', 10)->comment('Code d\'anonymat complet (Ex: TA1, TA2, SA1)');
            $table->decimal('note', 5, 2)->comment('Note obtenue');
            $table->foreignId('saisie_par')->comment('Utilisateur ayant saisi la note')
                  ->constrained('users')
                  ->onDelete('restrict');
            $table->timestamp('date_saisie')->useCurrent();
            $table->timestamps();

            // Chaque examen ne peut avoir qu'un seul code d'anonymat spécifique
            $table->unique(['examen_id', 'code_anonymat'], 'unique_copie_code');

            // Index pour la recherche rapide par code d'anonymat
            $table->index('code_anonymat', 'idx_copie_code');
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
