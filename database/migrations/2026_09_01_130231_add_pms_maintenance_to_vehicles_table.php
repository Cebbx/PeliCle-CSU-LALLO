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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->date('last_pms_date')->nullable()->after('status');
            $table->date('next_pms_date')->nullable()->after('last_pms_date');
            $table->text('maintenance_notes')->nullable()->after('next_pms_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['last_pms_date', 'next_pms_date', 'maintenance_notes']);
        });
    }
};
