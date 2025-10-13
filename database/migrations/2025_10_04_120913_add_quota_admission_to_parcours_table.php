<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ✅ Étape 1 : ajouter la colonne nullable (pas de default 0)
        Schema::table('parcours', function (Blueprint $table) {
            $table->string('quota_admission')->nullable()->after('is_active');
        });

        // ✅ Étape 2 : définir les quotas spécifiques PACES
        $niveauPACES = DB::table('niveaux')->where('abr', 'PACES')->first();

        if ($niveauPACES) {
            DB::table('parcours')
                ->where('niveau_id', $niveauPACES->id)
                ->update([
                    'quota_admission' => DB::raw("CASE 
                        WHEN abr = 'MG' THEN 160
                        WHEN abr = 'DENT' THEN 50
                        WHEN abr = 'INF-G' THEN 80
                        WHEN abr = 'INF-A' THEN 40
                        WHEN abr = 'MAI' THEN 60
                        WHEN abr = 'VET' THEN 30
                        WHEN abr = 'DIET' THEN 50
                        ELSE NULL
                    END")
                ]);
        }

        // ✅ Étape 3 : transformer les 0 existants en NULL
        DB::table('parcours')
            ->where('quota_admission', '=', 0)
            ->update(['quota_admission' => null]);
    }

    public function down()
    {
        // ✅ Étape inverse
        Schema::table('parcours', function (Blueprint $table) {
            $table->dropColumn('quota_admission');
        });
    }
};
