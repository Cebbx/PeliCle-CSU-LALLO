<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('campus_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default campus administration signatories
        $now = now();
        DB::table('campus_settings')->insert([
            [
                'key' => 'ceo_name',
                'value' => 'ENGR. JAMES B. CABILDO, PHD, ASEAN ENGR.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'ceo_title',
                'value' => 'Campus Executive Officer',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'gso_name',
                'value' => 'JOEL A. TUMAMAO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'gso_title',
                'value' => 'General Services Officer',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'cao_name',
                'value' => 'DR. ARLENE D. TALAMAYAN',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'cao_title',
                'value' => 'Chief Administrative Officer',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campus_settings');
    }
};
