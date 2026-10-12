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
        // 1. Ensure user ID 1 is the primary admin with admin@example.com
        DB::table('users')->where('id', 1)->update([
            'email' => 'admin@example.com',
            'role' => 'admin',
            'password' => Hash::make('password'),
            'plain_password' => 'password',
        ]);

        // 2. Also ensure thrishiabaletbet@gmail.com is registered as an admin so either email works
        $exists = DB::table('users')->where('email', 'thrishiabaletbet@gmail.com')->exists();
        if (! $exists) {
            DB::table('users')->insert([
                'name' => 'Admin (Trishia)',
                'email' => 'thrishiabaletbet@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'plain_password' => 'password',
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
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
