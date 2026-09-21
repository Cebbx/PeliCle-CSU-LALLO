<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_tickets', function (Blueprint $table) {
            $table->timestamp('gate_out_at')->nullable()->after('end_odometer');
            $table->timestamp('gate_in_at')->nullable()->after('gate_out_at');
            $table->string('scanned_by', 100)->nullable()->after('gate_in_at');
        });

        // Backfill existing trip tickets
        $tickets = DB::table('trip_tickets')->get();
        $guards = ['Guard 1', 'Guard 2', 'Guard 3'];

        foreach ($tickets as $ticket) {
            $outTime = $ticket->created_at;
            $inTime = $ticket->updated_at;
            $guardName = $guards[$ticket->id % 3];

            if ($ticket->vehicle_request_id) {
                $req = DB::table('vehicle_requests')->where('id', $ticket->vehicle_request_id)->first();
                if ($req && $req->date && $req->time) {
                    try {
                        $outTime = date('Y-m-d H:i:s', strtotime($req->date . ' ' . $req->time));
                    } catch (\Exception $e) {}
                }
            }

            DB::table('trip_tickets')->where('id', $ticket->id)->update([
                'gate_out_at' => $outTime,
                'gate_in_at' => $inTime,
                'scanned_by' => $guardName,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('trip_tickets', function (Blueprint $table) {
            $table->dropColumn(['gate_out_at', 'gate_in_at', 'scanned_by']);
        });
    }
};
