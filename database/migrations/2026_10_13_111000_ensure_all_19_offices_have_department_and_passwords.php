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
        $departments = [
            'ceo' => 'Office of the CEO (Campus Executive Officer)',
            'hrmo' => 'HRMO (Human Resource Management Office)',
            'accounting' => 'Accounting Office',
            'budget' => 'Budget Office',
            'property' => 'Property and Supply Office',
            'records' => 'Records Office',
            'planning' => 'Planning Office',
            'mis' => 'MIS Office (Management Information System / System Admin)',
            'registrar' => 'Office of the Campus Registrar',
            'admission' => 'Campus Admission Office',
            'publication' => 'Campus Publication Office',
            'library' => 'University Library',
            'cics' => 'CICS (College of Information and Computing Sciences)',
            'cte' => 'CTE (College of Teacher Education)',
            'chm' => 'CHM (College of Hospitality Management)',
            'coa' => 'COA (College of Agriculture)',
            'cafevalena' => 'Café Valena (CoffeeHub Café)',
            'csc' => 'Campus Student Council (CSC)',
            'dmst' => 'Department of Military Science and Tactics Office (DMST Office)',
        ];

        foreach ($departments as $code => $name) {
            $email = $code . '@csu.edu.ph';
            $user = DB::table('users')->where('email', $email)->first();

            if (! $user) {
                DB::table('users')->insert([
                    'name' => $name,
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => Hash::make($code),
                    'plain_password' => $code,
                    'role' => 'employee',
                    'department' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('users')->where('id', $user->id)->update([
                    'name' => $name,
                    'role' => 'employee',
                    'department' => $name,
                    'plain_password' => $code,
                ]);
            }
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
