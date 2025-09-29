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
        Schema::table('codes_anonymat', function (Blueprint $table) {
            $table->boolean('is_absent')
                ->default(false)
                ->after('sequence')
                ->comment('Indique si l\'étudiant est absent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('codes_anonymat', function (Blueprint $table) {
            $table->dropColumn('is_absent');
        });
    }
};
