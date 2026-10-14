<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('transactions:clear', function () {
    $this->info('Clearing all transactions (Vehicle Requests, Trip Tickets, Withdrawal Slips, Notifications)...');
    Artisan::call('db:seed', ['--class' => 'ClearTransactionsSeeder', '--force' => true]);
    $this->info('✓ Clean complete! All transaction tables are reset to 0, drivers & vehicles set to available.');
})->purpose('Clear all transaction data without deleting user accounts, drivers, or vehicles');

Artisan::command('transactions:seed', function () {
    $this->info('Generating realistic CSU Lal-lo test transactions...');
    Artisan::call('db:seed', ['--class' => 'TestDataSeeder', '--force' => true]);
    $this->info('✓ Test transactions successfully generated!');
})->purpose('Seed realistic test transactions (requests, trip tickets, slips) for evaluation');
