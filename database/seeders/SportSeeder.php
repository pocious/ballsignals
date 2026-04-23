<?php

namespace Database\Seeders;

use App\Models\Sport;
use Illuminate\Database\Seeder;

class SportSeeder extends Seeder
{
    public function run(): void
    {
        $sports = [
            ['name' => 'Football',    'slug' => 'football',    'icon' => '⚽'],
            ['name' => 'Basketball',  'slug' => 'basketball',  'icon' => '🏀'],
            ['name' => 'Tennis',      'slug' => 'tennis',      'icon' => '🎾'],
            ['name' => 'Baseball',    'slug' => 'baseball',    'icon' => '⚾'],
            ['name' => 'Hockey',      'slug' => 'hockey',      'icon' => '🏒'],
            ['name' => 'Rugby',       'slug' => 'rugby',       'icon' => '🏉'],
            ['name' => 'Cricket',     'slug' => 'cricket',     'icon' => '🏏'],
            ['name' => 'Volleyball',  'slug' => 'volleyball',  'icon' => '🏐'],
        ];

        foreach ($sports as $sport) {
            Sport::firstOrCreate(['slug' => $sport['slug']], $sport);
        }
    }
}
