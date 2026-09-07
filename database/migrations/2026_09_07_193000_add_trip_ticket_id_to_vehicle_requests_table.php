<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('trip_ticket_id')->nullable()->after('user_id')->index();
        });

        try {
            $tripTickets = DB::table('trip_tickets')->whereNotNull('vehicle_request_id')->get();
            foreach ($tripTickets as $ticket) {
                DB::table('vehicle_requests')
                    ->where('id', $ticket->vehicle_request_id)
                    ->update(['trip_ticket_id' => $ticket->id]);
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    public function down(): void
    {
        Schema::table('vehicle_requests', function (Blueprint $table) {
            $table->dropColumn('trip_ticket_id');
        });
    }
};
