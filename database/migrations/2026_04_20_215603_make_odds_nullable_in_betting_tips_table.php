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
            $table->decimal('odds', 6, 2)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('betting_tips', function (Blueprint $table) {
            $table->decimal('odds', 6, 2)->nullable(false)->change();
        });
    }
};
