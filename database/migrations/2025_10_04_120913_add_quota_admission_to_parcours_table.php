<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('parcours', function (Blueprint $table) {
            $table->integer('quota_admission')->default(0)->after('is_active');
        });

        // Définir les quotas pour PACES
        $niveauPACES = DB::table('niveaux')->where('abr', 'PACES')->first();
        
        if ($niveauPACES) {
            DB::table('parcours')->where('niveau_id', $niveauPACES->id)->update([
                'quota_admission' => DB::raw("CASE 
                    WHEN abr = 'MG' THEN 160
                    WHEN abr = 'DENT' THEN 50
                    WHEN abr = 'INF-G' THEN 80
                    WHEN abr = 'INF-A' THEN 40
                    WHEN abr = 'MAI' THEN 60
                    WHEN abr = 'VET' THEN 30
                    WHEN abr = 'DIET' THEN 50
                    ELSE 0
                END")
            ]);
        }
    }

    public function down()
    {
        Schema::table('parcours', function (Blueprint $table) {
            $table->dropColumn('quota_admission');
        });
    }
};