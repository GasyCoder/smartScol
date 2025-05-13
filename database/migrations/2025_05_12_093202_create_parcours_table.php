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
        Schema::create('parcours', function (Blueprint $table) {
            $table->id();
            $table->string('abr', 10)->comment('Ex: MG, DENT, INF');
            $table->string('nom', 100)->comment('Ex: Médecine générale, Dentaire, Infirmier');
            $table->unsignedBigInteger('niveau_id')->comment('Niveau auquel appartient ce parcours');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('niveau_id')->references('id')->on('niveaux')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcours');
    }
};
