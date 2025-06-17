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
            $table->decimal('credits', 5, 2)->default(0)
                  ->comment('Nombre de crédits associés à cette UE');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('niveau_id');
            $table->unsignedBigInteger('parcours_id')->nullable()->comment('Uniquement pour les UE spécifiques à un parcours (PACES)');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('niveau_id')->references('id')->on('niveaux')->onDelete('cascade');
            $table->foreign('parcours_id')->references('id')->on('parcours')->onDelete('cascade');
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
