<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resultats_finaux', function (Blueprint $table) {
            $table->boolean('is_deliber')->default(false)->after('jury_validated')
                ->comment('Indique si une délibération a été appliquée pour ce résultat');
            $table->timestamp('deliber_at')->nullable()->after('is_deliber')
                ->comment('Date et heure de la délibération');
            $table->unsignedBigInteger('deliber_by')->nullable()->after('deliber_at')
                ->comment('Utilisateur ayant appliqué la délibération');
            
            $table->foreign('deliber_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('resultats_finaux', function (Blueprint $table) {
            $table->dropForeign(['deliber_by']);
            $table->dropColumn(['is_deliber', 'deliber_at', 'deliber_by']);
        });
    }
};