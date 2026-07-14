<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autocomplete_entries', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);   // 'team' or 'league'
            $table->string('name', 150);
            $table->unique(['type', 'name']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autocomplete_entries');
    }
};
