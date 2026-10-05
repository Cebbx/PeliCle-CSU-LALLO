<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('guard_id', 50)->nullable()->unique()->after('role');
            $table->string('contact_number', 50)->nullable()->after('guard_id');
            $table->string('position', 100)->nullable()->after('contact_number');
        });

        // Seed default security guard records if not present
        $defaultGuards = [
            [
                'name' => 'Edward Cabbat',
                'email' => 'edwarddufale3@gmail.com',
                'role' => 'guard',
                'guard_id' => '1001',
                'position' => 'Lead Gate Guard',
                'department' => 'Security & Safety Office',
                'contact_number' => '09123456781',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Monhel Bumagat',
                'email' => 'bumagatmonhelmirafuente@gmail.com',
                'role' => 'guard',
                'guard_id' => '1002',
                'position' => 'Gate Security Officer',
                'department' => 'Security & Safety Office',
                'contact_number' => '09123456782',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Matthew Baldera',
                'email' => 'balderajohnmatthew@gmail.com',
                'role' => 'guard',
                'guard_id' => '1003',
                'position' => 'Gate Security Officer',
                'department' => 'Security & Safety Office',
                'contact_number' => '09123456783',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($defaultGuards as $guard) {
            if (!DB::table('users')->where('email', $guard['email'])->exists()) {
                DB::table('users')->insert(array_merge($guard, [
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            } else {
                DB::table('users')->where('email', $guard['email'])->update([
                    'guard_id' => $guard['guard_id'],
                    'position' => $guard['position'],
                    'contact_number' => $guard['contact_number'],
                    'role' => 'guard',
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['guard_id', 'contact_number', 'position']);
        });
    }
};
