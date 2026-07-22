<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Clear broken local paths so next news:fetch-football stores direct CDN URLs instead
        DB::table('football_news')
            ->where('image', 'like', '/storage/news/%')
            ->orWhere('image', 'like', '/images/news/%')
            ->update(['image' => null]);
    }

    public function down(): void {}
};
