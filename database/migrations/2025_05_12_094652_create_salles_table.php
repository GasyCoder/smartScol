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
        Schema::create('salles', function (Blueprint $table) {
            $table->id();
            $table->string('code_base', 5)->nullable()->comment('Première lettre du préfixe (T pour 2P, S pour 2P1, etc.)');
            $table->string('nom', 50)->comment('Ex: 2P, 2P1');
            $table->integer('capacite')->comment('Nombre de places');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salles');
    }
};
