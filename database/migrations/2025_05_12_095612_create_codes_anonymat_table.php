<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('codes_anonymat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examen_id')->comment('Examen concerné');
            $table->unsignedBigInteger('ec_id')->nullable();
            $table->string('code_complet', 20)->comment('Code complet d\'anonymat (Ex: TA1, SA2)');
            $table->integer('sequence')->nullable()->comment('Numéro séquentiel (Ex: 1 dans TA1)');
            $table->timestamps();
            $table->softDeletes();

            // Contraintes
            $table->foreign('examen_id')->references('id')->on('examens')->onDelete('cascade');
            $table->foreign('ec_id')->references('id')->on('ecs');

            // Unicité
            $table->unique(['examen_id', 'ec_id', 'code_complet']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('codes_anonymat');
    }
};
