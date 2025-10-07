<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliber_paces', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('niveau_id');
            $table->unsignedBigInteger('parcours_id');
            $table->unsignedBigInteger('session_exam_id');
            $table->integer('quota_admission')->nullable()->comment('Quota d\'admission appliqué');
            $table->integer('credits_requis')->default(60)->comment('Crédits requis');
            $table->decimal('moyenne_requise', 5, 2)->default(10.00)->comment('Moyenne minimale');
            $table->boolean('note_eliminatoire')->default(true)->comment('Note 0 éliminatoire');
            $table->integer('nb_admis')->default(0)->comment('Nombre d\'admis');
            $table->integer('nb_redoublants')->default(0)->comment('Nombre de redoublants');
            $table->integer('nb_exclus')->default(0)->comment('Nombre d\'exclus');
            $table->unsignedBigInteger('applique_par')->comment('Utilisateur ayant appliqué');
            $table->timestamp('applique_at')->comment('Date d\'application');
            $table->timestamps();

            $table->foreign('niveau_id')->references('id')->on('niveaux')->onDelete('cascade');
            $table->foreign('parcours_id')->references('id')->on('parcours')->onDelete('cascade');
            $table->foreign('session_exam_id')->references('id')->on('session_exams')->onDelete('cascade');
            $table->foreign('applique_par')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['niveau_id', 'parcours_id', 'session_exam_id'], 'idx_deliber_context');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliber_paces');
    }
};