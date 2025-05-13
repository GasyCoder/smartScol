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
        Schema::create('decisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deliberation_id')->comment('Délibération concernée');
            $table->unsignedBigInteger('etudiant_id')->comment('Étudiant concerné');
            $table->decimal('moyenne', 5, 2)->comment('Moyenne générale');
            $table->enum('decision', ['admis', 'ajourne', 'admis_conditionnellement', 'redouble']);
            $table->decimal('points_jury', 5, 2)->default(0)->comment('Points ajoutés par le jury');
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->foreign('deliberation_id')->references('id')->on('deliberations')->onDelete('cascade');
            $table->foreign('etudiant_id')->references('id')->on('etudiants')->onDelete('cascade');

            $table->unique(['deliberation_id', 'etudiant_id']); // Une décision unique par délibération/étudiant
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decisions');
    }
};
