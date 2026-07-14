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
            $table->string('home_form', 10)->nullable()->after('reasoning');
            $table->string('away_form', 10)->nullable()->after('home_form');
        });
    }

    public function down(): void
    {
        Schema::table('betting_tips', function (Blueprint $table) {
            $table->dropColumn(['home_form', 'away_form']);
        });
    }
};
