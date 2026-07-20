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
        Schema::create('football_news', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url');
            $table->string('image')->nullable();
            $table->string('source');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('football_news');
    }
};
