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
        Schema::create('ecs', function (Blueprint $table) {
            $table->id();
            $table->string('abr', 10)->nullable()->comment('Ex: EC1, EC2');
            $table->string('nom', 100)->comment('Ex: Anatomie, Histologie');
            $table->decimal('coefficient', 3, 1)->default(1.0);
            $table->unsignedBigInteger('ue_id')->comment('UE à laquelle appartient l\'EC');
            $table->string('enseignant')->comment('Enseignant responsable de l\'EC');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ue_id')->references('id')->on('ues')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecs');
    }
};
