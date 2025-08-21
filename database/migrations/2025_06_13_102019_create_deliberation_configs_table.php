<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deliberation_configs', function (Blueprint $table) {
            $table->id();

            // ✅ ASSOCIATIONS SIMPLES
            $table->unsignedBigInteger('niveau_id');
            $table->unsignedBigInteger('parcours_id')->nullable();
            $table->unsignedBigInteger('session_id');

            // ✅ PARAMÈTRES DÉLIBÉRATION (simplifié logique médecine)
            $table->integer('credits_admission_s1')->default(60);
            $table->integer('credits_admission_s2')->default(40);
            $table->integer('credits_redoublement_s2')->default(20);

            // ✅ RÈGLES NOTE ÉLIMINATOIRE
            $table->boolean('note_eliminatoire_bloque_s1')->default(true);
            $table->boolean('note_eliminatoire_exclusion_s2')->default(true);

            // ✅ ÉTAT DÉLIBÉRATION
            $table->boolean('delibere')->default(false);
            $table->timestamp('date_deliberation')->nullable();
            $table->unsignedBigInteger('delibere_par')->nullable();

            $table->timestamps();

            // ✅ CONTRAINTES
            $table->foreign('niveau_id')->references('id')->on('niveaux')->onDelete('cascade');
            $table->foreign('parcours_id')->references('id')->on('parcours')->onDelete('cascade');
            $table->foreign('session_id')->references('id')->on('session_exams')->onDelete('cascade');
            $table->foreign('delibere_par')->references('id')->on('users')->onDelete('set null');

            // ✅ INDEX UNIQUE
            $table->unique(['niveau_id', 'parcours_id', 'session_id'], 'idx_deliberation_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deliberation_configs');
    }
};