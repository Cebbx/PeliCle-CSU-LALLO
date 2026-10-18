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
        // 1. Ensure the primary admin account (id: 1 or admin@example.com) is named Joel Tumamao
        DB::table('users')->where('id', 1)->orWhere('email', 'admin@example.com')->update([
            'name' => 'Joel Tumamao',
            'role' => 'admin',
            'guard_id' => null,
            'position' => 'Campus Administrator / GSO',
            'department' => 'General Services Office (GSO)',
        ]);

        // 2. Also ensure other admin login variations use Joel Tumamao
        DB::table('users')->whereIn('email', ['thrishiabaletbet@gmail.com', 'trishiabaletbet@gmail.com'])->update([
            'name' => 'Joel Tumamao',
            'role' => 'admin',
        ]);

        // 3. Ensure Edward Cabbat exists as Lead Gate Guard (ID: 1001)
        $guardExists = DB::table('users')->where('email', 'edwarddufale3@gmail.com')->orWhere('guard_id', '1001')->first();
        if (! $guardExists) {
            DB::table('users')->insert([
                'name' => 'Edward Cabbat',
                'email' => 'edwarddufale3@gmail.com',
                'role' => 'guard',
                'guard_id' => '1001',
                'position' => 'Lead Gate Guard',
                'department' => 'Security & Safety Office',
                'contact_number' => '09123456781',
                'password' => Hash::make('password'),
                'plain_password' => 'password',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')->where('id', $guardExists->id)->update([
                'name' => 'Edward Cabbat',
                'role' => 'guard',
                'guard_id' => '1001',
                'position' => 'Lead Gate Guard',
                'department' => 'Security & Safety Office',
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
