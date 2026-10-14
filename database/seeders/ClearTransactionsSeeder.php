<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\TripTicket;
use App\Models\Vehicle;
use App\Models\VehicleRequest;
use App\Models\WithdrawalSlip;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearTransactionsSeeder extends Seeder
{
    /**
     * Clear all transaction records (Requests, Trip Tickets, Slips, Logs)
     * Leaves all Users, Drivers, Vehicles, and Settings intact.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Clear transaction tables
        WithdrawalSlip::query()->truncate();
        TripTicket::query()->truncate();
        VehicleRequest::query()->truncate();

        // 2. Clear related logs and notifications if tables exist
        if (Schema::hasTable('notifications')) {
            DB::table('notifications')->truncate();
        }
        if (Schema::hasTable('activity_logs')) {
            DB::table('activity_logs')->truncate();
        }
        if (Schema::hasTable('sms_logs')) {
            DB::table('sms_logs')->truncate();
        }

        Schema::enableForeignKeyConstraints();

        // 3. Reset all Drivers & Vehicles back to Available
        Driver::query()->update(['status' => 'available']);
        Vehicle::query()->update(['status' => 'available']);

        $this->command->info('✓ All transactions successfully cleared! Drivers and vehicles are now available.');
    }
}
