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
            $table->enum('type', ['Normale', 'Rattrapage'])->default('Normale');
            $table->unsignedBigInteger('annee_universitaire_id');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_current')->default(false);
            $table->date('date_start');
            $table->date('date_end');
            $table->boolean('deliberation_appliquee')->default(false);
            $table->timestamp('date_deliberation')->nullable();
            $table->unsignedBigInteger('delibere_par')->nullable();
            $table->json('parametres_deliberation')->nullable();
            $table->text('observations_deliberation')->nullable();
            $table->json('historique_deliberations')->nullable();
            
            $table->foreign('delibere_par')->references('id')->on('users');
            $table->timestamps();

            $table->foreign('annee_universitaire_id')->references('id')->on('annees_universitaires')->onDelete('cascade');
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
