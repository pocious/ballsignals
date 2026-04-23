<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('betting_tips', function (Blueprint $table) {
            $table->string('sport')->default('Football')->after('id');
            $table->index('sport');
        });
    }

    public function down(): void
    {
        Schema::table('betting_tips', function (Blueprint $table) {
            $table->dropIndex(['sport']);
            $table->dropColumn('sport');
        });
    }
};
