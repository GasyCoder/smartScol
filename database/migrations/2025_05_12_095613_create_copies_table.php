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
        Schema::create('copies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examen_id')->comment('Examen concerné');
            $table->unsignedBigInteger('ec_id')->comment('Élément constitutif concerné');
            $table->unsignedBigInteger('code_anonymat_id')->comment('Référence au code d\'anonymat');
            $table->decimal('note', 5, 2)->comment('Note obtenue');
            $table->unsignedBigInteger('saisie_par')->comment('Utilisateur ayant saisi la note');
            $table->timestamp('date_saisie')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['examen_id', 'code_anonymat_id']);

            $table->foreign('examen_id')->references('id')->on('examens');
            $table->foreign('ec_id')->references('id')->on('ecs');
            $table->foreign('code_anonymat_id')->references('id')->on('codes_anonymat')->onDelete('cascade');
            $table->foreign('saisie_par')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('copies');
    }
};
