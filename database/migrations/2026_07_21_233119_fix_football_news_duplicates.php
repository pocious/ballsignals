<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate rows — keep the one with the lowest id (earliest stored)
        DB::statement("
            DELETE n1 FROM football_news n1
            INNER JOIN football_news n2
            WHERE n1.id > n2.id AND n1.url = n2.url
        ");

        // Prevent duplicates going forward
        Schema::table('football_news', function (Blueprint $table) {
            $table->unique('url');
        });
    }

    public function down(): void
    {
        Schema::table('football_news', function (Blueprint $table) {
            $table->dropUnique(['url']);
        });
    }
};
