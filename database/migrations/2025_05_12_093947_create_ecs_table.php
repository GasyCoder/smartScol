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
            $table->decimal('coefficient', 5, 2)->default(1)->comment('Coefficient de pondération');
            $table->unsignedBigInteger('ue_id')->comment('UE à laquelle appartient l\'EC');
            $table->unsignedBigInteger('enseignant_id')->comment('Enseignant responsable de l\'EC');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ue_id')->references('id')->on('ues')->onDelete('restrict');
            $table->foreign('enseignant_id')->references('id')->on('users')->onDelete('restrict');
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
