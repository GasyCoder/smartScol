<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('annees_universitaires', function (Blueprint $table) {
            $table->string('libelle', 20)->after('id')->nullable();
        });
        
        // Remplir automatiquement les libellés existants
        DB::statement("
            UPDATE annees_universitaires 
            SET libelle = CONCAT(YEAR(date_start), '-', YEAR(date_end))
            WHERE libelle IS NULL
        ");
        
        // Rendre la colonne non nullable après remplissage
        Schema::table('annees_universitaires', function (Blueprint $table) {
            $table->string('libelle', 20)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('annees_universitaires', function (Blueprint $table) {
            $table->dropColumn('libelle');
        });
    }
};