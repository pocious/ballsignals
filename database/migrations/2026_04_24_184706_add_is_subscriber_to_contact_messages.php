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
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->boolean('is_subscriber')->default(false)->after('is_read');
        });

        // Mark existing newsletter subscriptions
        \Illuminate\Support\Facades\DB::table('contact_messages')
            ->where('subject', 'Daily Tips Subscription')
            ->update(['is_subscriber' => true]);
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropColumn('is_subscriber');
        });
    }
};
