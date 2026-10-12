<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampusSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Retrieve a setting value by key with an optional default.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::where('key', $key)->first();
        return ($setting && filled($setting->value)) ? $setting->value : $default;
    }

    /**
     * Store or update a setting value by key.
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
