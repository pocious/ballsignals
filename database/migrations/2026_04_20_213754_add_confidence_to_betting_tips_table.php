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
            $table->tinyInteger('confidence')->unsigned()->nullable()->after('prediction'); // 1–5
        });
    }

    public function down(): void
    {
        Schema::table('betting_tips', function (Blueprint $table) {
            $table->dropColumn('confidence');
        });
    }
};
