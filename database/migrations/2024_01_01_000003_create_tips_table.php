<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();   // admin who posted
            $table->foreignId('sport_id')->constrained()->cascadeOnDelete();
            $table->date('match_date');
            $table->string('home_team');
            $table->string('away_team');
            $table->string('league')->nullable();
            $table->string('prediction');         // e.g. "Home Win", "Over 2.5", "BTTS"
            $table->string('tip_type');           // e.g. "1X2", "Over/Under", "Both Teams Score"
            $table->decimal('odds', 6, 2);
            $table->tinyInteger('confidence')->unsigned()->nullable(); // 1–5 stars
            $table->enum('status', ['pending', 'won', 'lost', 'void'])->default('pending');
            $table->text('notes')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['match_date', 'is_published']);
            $table->index(['sport_id', 'match_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tips');
    }
};
