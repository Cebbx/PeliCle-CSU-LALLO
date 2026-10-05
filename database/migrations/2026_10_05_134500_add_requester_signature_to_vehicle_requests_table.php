<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_requests', function (Blueprint $table) {
            $table->longText('requester_signature')->nullable()->after('employee_name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->longText('signature')->nullable()->after('department');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_requests', function (Blueprint $table) {
            $table->dropColumn('requester_signature');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('signature');
        });
    }
};
