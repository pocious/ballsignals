<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('betting_tips', function (Blueprint $table) {
            $table->id();
            $table->string('home_team');
            $table->string('away_team');
            $table->string('league')->nullable();
            $table->string('prediction');
            $table->decimal('odds', 6, 2);
            $table->dateTime('match_time');
            $table->enum('status', ['pending', 'won', 'lost'])->default('pending');
            $table->timestamps();

            $table->index(['match_time', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('betting_tips');
    }
};
