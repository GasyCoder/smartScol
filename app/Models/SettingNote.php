<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingNote extends Model
{
    protected $table = 'setting_notes';

    protected $fillable = ['is_enabled'];

    public static function isActive()
    {
        $setting = self::first();
        return $setting ? $setting->is_enabled : false;
    }
}
