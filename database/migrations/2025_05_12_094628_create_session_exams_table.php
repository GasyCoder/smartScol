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
        Schema::create('session_exams', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->comment('Ex: S1, S2, RAT (rattrapage)');
            $table->string('nom', 100)->comment('Ex: Première session, Session de rattrapage');
            $table->unsignedBigInteger('annee_universitaire_id');
            $table->enum('type', ['normale', 'rattrapage', 'concours'])->default('normale');
            $table->date('date_start');
            $table->date('date_end');
            $table->timestamps();

            $table->foreign('annee_universitaire_id')->references('id')->on('annees_universitaires')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_exams');
    }
};
