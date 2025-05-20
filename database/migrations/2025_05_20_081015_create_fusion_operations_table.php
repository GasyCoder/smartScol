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
        Schema::create('fusion_operations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('examen_id');
            $table->unsignedBigInteger('user_id');
            $table->string('type');  // 'coherence', 'fusion', 'validation', 'publication'
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('parameters')->nullable(); // Paramètres utilisés
            $table->json('result')->nullable(); // Résultat de l'opération
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamps();

            $table->foreign('examen_id')->references('id')->on('examens')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['examen_id', 'type', 'status'], 'unique_pending_operation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fusion_operations');
    }
};
