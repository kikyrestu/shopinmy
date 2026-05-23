<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $service = new \App\Services\MyParcelService();
    $order = \App\Models\Order::first(); // Just get the first order
    if ($order) {
        echo "Generating AWB for Order #" . $order->order_number . "...\n";
        $trackingNo = $service->generateAwbForOrder($order);
        echo "Success! Tracking No: " . $trackingNo . "\n";
    } else {
        echo "No orders found.\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
