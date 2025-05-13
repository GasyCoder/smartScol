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
      Schema::create('ues', function (Blueprint $table) {
            $table->id();
            $table->string('abr', 10)->nullable()->comment('Ex: UE1, UE2');
            $table->string('nom', 100)->comment('Ex: Médecine humaine, Physiologie');
            $table->unsignedBigInteger('niveau_id');
            $table->unsignedBigInteger('parcours_id')->nullable()->comment('Uniquement pour les UE spécifiques à un parcours (PACES)');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('niveau_id')->references('id')->on('niveaux')->onDelete('restrict');
            $table->foreign('parcours_id')->references('id')->on('parcours')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ues');
    }
};
