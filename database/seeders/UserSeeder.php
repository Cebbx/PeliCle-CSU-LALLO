<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'email' => 'admin@example.com',
                'name' => 'JOEL A. TUMAMAO',
                'password' => Hash::make('password'),
                'plain_password' => 'password',
                'role' => 'admin',
            ],
            [
                'email' => 'thrishiabaletbet@gmail.com',
                'name' => 'Admin (Trishia)',
                'password' => Hash::make('password'),
                'plain_password' => 'password',
                'role' => 'admin',
            ],
            [
                'email' => 'trishiabaletbet@gmail.com',
                'name' => 'Admin (Trishia)',
                'password' => Hash::make('password'),
                'plain_password' => 'password',
                'role' => 'admin',
            ],
            [
                'email' => 'employee@example.com',
                'name' => 'Employee User',
                'password' => Hash::make('password'),
                'plain_password' => 'password',
                'role' => 'employee',
            ],
        ];

        foreach ($users as $data) {
            $user = User::withTrashed()->where('email', $data['email'])->first();
            if ($user) {
                if ($user->trashed()) {
                    $user->restore();
                }
                $user->update($data);
            } else {
                User::create(array_merge($data, [
                    'email_verified_at' => now(),
                ]));
            }
        }
    }
}
