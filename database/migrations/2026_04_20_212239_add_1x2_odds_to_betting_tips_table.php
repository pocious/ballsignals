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
            $table->string('country')->nullable()->after('league');
            $table->decimal('home_odds', 6, 2)->nullable()->after('odds');
            $table->decimal('draw_odds', 6, 2)->nullable()->after('home_odds');
            $table->decimal('away_odds', 6, 2)->nullable()->after('draw_odds');
        });
    }

    public function down(): void
    {
        Schema::table('betting_tips', function (Blueprint $table) {
            $table->dropColumn(['country', 'home_odds', 'draw_odds', 'away_odds']);
        });
    }
};
