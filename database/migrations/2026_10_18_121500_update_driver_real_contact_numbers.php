<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Driver;

return new class extends Migration
{
    /**
     * Run the migrations to ensure real contact numbers are saved for the drivers.
     */
    public function up(): void
    {
        $numbers = [
            'Joel' => '09683440163',
            'Lucio' => '09982610555',
            'Rey' => '09682333960',
            'Norman' => '09496534358',
        ];

        foreach ($numbers as $namePrefix => $phone) {
            Driver::where('name', 'like', "{$namePrefix}%")->update([
                'contact_number' => $phone,
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
