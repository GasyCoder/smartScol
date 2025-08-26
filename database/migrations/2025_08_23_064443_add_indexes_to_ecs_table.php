<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table = 'ecs';

    public function up(): void
    {
        // 1) Ajouter les colonnes manquantes (nullable) — non destructif
        Schema::table($this->table, function (Blueprint $table) {
            if (!Schema::hasColumn($this->table, 'niveau_id')) {
                $table->unsignedBigInteger('niveau_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn($this->table, 'parcours_id')) {
                $table->unsignedBigInteger('parcours_id')->nullable()->after('niveau_id');
            }
            // On suppose que 'abr' existe déjà. Si ce n’est pas le cas, décommente :
            // if (!Schema::hasColumn($this->table, 'abr')) {
            //     $table->string('abr', 50)->nullable()->after('id');
            // }
        });

        // 2) Créer les index seulement s’ils n’existent pas et si la colonne existe
        $this->addIndexIfPossible('ecs_niveau_id_index',  'niveau_id');
        $this->addIndexIfPossible('ecs_parcours_id_index','parcours_id');
        $this->addIndexIfPossible('ecs_abr_index',        'abr');

        // (Optionnel) Tu pourras plus tard ajouter des clés étrangères si les données sont cohérentes :
        // Schema::table($this->table, function (Blueprint $table) {
        //     $table->foreign('niveau_id')->references('id')->on('niveaux')->nullOnDelete();
        //     $table->foreign('parcours_id')->references('id')->on('parcours')->nullOnDelete();
        // });
    }

    public function down(): void
    {
        // On ne supprime que les index (rollback non destructif)
        $this->dropIndexIfExists('ecs_niveau_id_index');
        $this->dropIndexIfExists('ecs_parcours_id_index');
        $this->dropIndexIfExists('ecs_abr_index');

        // On NE supprime PAS les colonnes pour éviter toute perte de données potentielle.
        // Si tu veux les retirer lors du rollback, décommente prudemment :
        // Schema::table($this->table, function (Blueprint $table) {
        //     if (Schema::hasColumn($this->table, 'niveau_id'))   $table->dropColumn('niveau_id');
        //     if (Schema::hasColumn($this->table, 'parcours_id')) $table->dropColumn('parcours_id');
        // });
    }

    private function addIndexIfPossible(string $indexName, string $column): void
    {
        if (!Schema::hasColumn($this->table, $column)) {
            return; // pas de colonne -> pas d’index
        }
        if ($this->indexExists($indexName)) {
            return; // index déjà présent
        }
        Schema::table($this->table, function (Blueprint $table) use ($column, $indexName) {
            $table->index($column, $indexName);
        });
    }

    private function dropIndexIfExists(string $indexName): void
    {
        if (! $this->indexExists($indexName)) return;
        Schema::table($this->table, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    private function indexExists(string $indexName): bool
    {
        $row = DB::selectOne(
            'SELECT COUNT(1) AS c
               FROM information_schema.statistics
              WHERE table_schema = DATABASE()
                AND table_name = ?
                AND index_name = ?',
            [$this->table, $indexName]
        );
        return (int)($row->c ?? 0) > 0;
    }
};
