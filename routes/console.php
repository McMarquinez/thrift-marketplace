<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\InventoryService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:expire-pending', function (InventoryService $inventoryService) {
    $count = $inventoryService->expirePendingOrders();

    $this->info("Expired {$count} pending order(s).");
})->purpose('Expire pending-payment orders whose reservation window elapsed');

Schedule::command('orders:expire-pending')->everyMinute();
