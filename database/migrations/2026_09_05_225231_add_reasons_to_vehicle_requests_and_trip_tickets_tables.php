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
        Schema::table('vehicle_requests', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('status');
            $table->text('cancellation_reason')->nullable()->after('rejection_reason');
        });

        Schema::table('trip_tickets', function (Blueprint $table) {
            $table->text('cancellation_reason')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_requests', function (Blueprint $table) {
            $table->dropColumn(['rejection_reason', 'cancellation_reason']);
        });

        Schema::table('trip_tickets', function (Blueprint $table) {
            $table->dropColumn(['cancellation_reason']);
        });
    }
};
