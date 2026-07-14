<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('betting_tips', function (Blueprint $table) {
            $table->text('reasoning')->nullable()->after('is_premium');
        });
    }

    public function down(): void
    {
        Schema::table('betting_tips', function (Blueprint $table) {
            $table->dropColumn('reasoning');
        });
    }
};
