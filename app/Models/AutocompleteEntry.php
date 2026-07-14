<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutocompleteEntry extends Model
{
    protected $fillable = ['type', 'name'];

    public static function remember(string $type, string $value): void
    {
        $value = trim($value);
        if ($value !== '') {
            static::firstOrCreate(['type' => $type, 'name' => $value]);
        }
    }
}
