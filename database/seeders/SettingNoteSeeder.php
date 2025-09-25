<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SettingNote;

class SettingNoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SettingNote::firstOrCreate(
            ['id' => 1],
            ['is_enabled' => false]
        );
    }
}