<?php

namespace App\Services;

use App\Models\Setting as ModelsSetting;

class Setting
{
    public static function get(string $name)
    {
        if ($name) {
            return ModelsSetting::get($name);
        }
    }
}
