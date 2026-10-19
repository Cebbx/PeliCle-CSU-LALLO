<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vehicle_requests', 'document_verified_at')) {
            Schema::table('vehicle_requests', function (Blueprint $table) {
                $table->timestamp('document_verified_at')->nullable()->after('document');
            });
        }

        if (!Schema::hasColumn('trip_tickets', 'document_verified_at')) {
            Schema::table('trip_tickets', function (Blueprint $table) {
                $table->timestamp('document_verified_at')->nullable()->after('document');
            });
        }

        // For existing completed or on_trip requests that already have documents, mark as verified
        \Illuminate\Support\Facades\DB::table('vehicle_requests')
            ->whereNotNull('document')
            ->whereIn('status', ['on_trip', 'completed'])
            ->update(['document_verified_at' => now()]);

        \Illuminate\Support\Facades\DB::table('trip_tickets')
            ->whereNotNull('document')
            ->whereIn('status', ['active', 'completed'])
            ->update(['document_verified_at' => now()]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('vehicle_requests', 'document_verified_at')) {
            Schema::table('vehicle_requests', function (Blueprint $table) {
                $table->dropColumn('document_verified_at');
            });
        }

        if (Schema::hasColumn('trip_tickets', 'document_verified_at')) {
            Schema::table('trip_tickets', function (Blueprint $table) {
                $table->dropColumn('document_verified_at');
            });
        }
    }
};
