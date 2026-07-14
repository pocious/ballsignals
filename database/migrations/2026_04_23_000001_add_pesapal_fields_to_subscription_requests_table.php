<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->string('pesapal_merchant_ref')->nullable()->after('notes');
            $table->string('pesapal_tracking_id')->nullable()->after('pesapal_merchant_ref');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->dropColumn(['pesapal_merchant_ref', 'pesapal_tracking_id']);
        });
    }
};
