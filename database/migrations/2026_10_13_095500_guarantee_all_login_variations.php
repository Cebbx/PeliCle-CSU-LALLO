<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure admin@example.com exists
        DB::table('users')->where('id', 1)->update([
            'email' => 'admin@example.com',
            'role' => 'admin',
            'password' => Hash::make('password'),
            'plain_password' => 'password',
        ]);

        // 2. Ensure thrishiabaletbet@gmail.com (with h) exists
        $hasWithH = DB::table('users')->where('email', 'thrishiabaletbet@gmail.com')->exists();
        if (! $hasWithH) {
            DB::table('users')->insert([
                'name' => 'JOEL A. TUMAMAO (Admin)',
                'email' => 'thrishiabaletbet@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'plain_password' => 'password',
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')->where('email', 'thrishiabaletbet@gmail.com')->update([
                'role' => 'admin',
                'password' => Hash::make('password'),
                'plain_password' => 'password',
            ]);
        }

        // 3. Ensure trishiabaletbet@gmail.com (without h) also exists so typos never lock out!
        $hasWithoutH = DB::table('users')->where('email', 'trishiabaletbet@gmail.com')->exists();
        if (! $hasWithoutH) {
            DB::table('users')->insert([
                'name' => 'JOEL A. TUMAMAO (Admin)',
                'email' => 'trishiabaletbet@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'plain_password' => 'password',
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')->where('email', 'trishiabaletbet@gmail.com')->update([
                'role' => 'admin',
                'password' => Hash::make('password'),
                'plain_password' => 'password',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
