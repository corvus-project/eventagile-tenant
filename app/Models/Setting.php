<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $guarded = [];

    public static function get(string $name): string
    {
        $setting = self::query()
            ->where('name', $name)
            ->first('payload');
        if ($setting) {
            return $setting->getAttribute('payload');
        }
        return '';
    }
}
