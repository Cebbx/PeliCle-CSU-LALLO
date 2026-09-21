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
        Schema::table('withdrawal_slips', function (Blueprint $table) {
            $table->string('driver_name')->nullable()->after('trip_ticket_id');
            $table->string('vehicle_name')->nullable()->after('driver_name');
            $table->string('destination_address')->nullable()->after('vehicle_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_slips', function (Blueprint $table) {
            $table->dropColumn(['driver_name', 'vehicle_name', 'destination_address']);
        });
    }
};
