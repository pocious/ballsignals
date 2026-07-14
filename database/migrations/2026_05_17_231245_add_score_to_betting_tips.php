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
        Schema::table('betting_tips', function (Blueprint $table) {
            $table->unsignedTinyInteger('home_score')->nullable()->after('away_form');
            $table->unsignedTinyInteger('away_score')->nullable()->after('home_score');
        });
    }

    public function down(): void
    {
        Schema::table('betting_tips', function (Blueprint $table) {
            $table->dropColumn(['home_score', 'away_score']);
        });
    }
};
