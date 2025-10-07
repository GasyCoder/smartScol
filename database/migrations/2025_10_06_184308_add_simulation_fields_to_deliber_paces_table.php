<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliber_paces', function (Blueprint $table) {
            // Type : 'simulation' ou 'deliberation'
            $table->enum('type', ['simulation', 'deliberation'])->default('deliberation')->after('id');
            
            // Pour les simulations en cours
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->nullable()->after('type');
            $table->integer('progress')->default(0)->after('status'); // 0-100
            
            // Stockage des résultats
            $table->longText('resultats')->nullable()->after('nb_exclus');
            $table->text('groupes')->nullable()->after('resultats');
            $table->text('statistiques')->nullable()->after('groupes');
            
            // Gestion des erreurs
            $table->text('error_message')->nullable()->after('statistiques');
            $table->decimal('duree_secondes', 8, 2)->nullable()->after('error_message');
            
            // Modifier applique_at pour être nullable (pour les simulations en cours)
            $table->timestamp('applique_at')->nullable()->change();
            
            // Index pour les simulations
            $table->index(['applique_par', 'type', 'status'], 'idx_user_simulations');
        });
    }

    public function down(): void
    {
        Schema::table('deliber_paces', function (Blueprint $table) {
            $table->dropIndex('idx_user_simulations');
            $table->dropColumn([
                'type',
                'status',
                'progress',
                'resultats',
                'groupes',
                'statistiques',
                'error_message',
                'duree_secondes'
            ]);
        });
    }
};