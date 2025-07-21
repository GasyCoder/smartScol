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
        Schema::create('niveaux', function (Blueprint $table) {
            $table->id();
            $table->string('abr', 10)->comment('Ex: PACES, L2, L3...');
            $table->string('nom', 100);
            $table->boolean('has_parcours')->default(false)->comment('Indique si ce niveau a des parcours');
            $table->boolean('has_rattrapage')->default(true)->comment('Indique si ce niveau a une session de rattrapage');
            $table->boolean('is_concours')->default(false)->comment('Indique si ce niveau est sous forme de concours');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('niveaux');
    }
};
