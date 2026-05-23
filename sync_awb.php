<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $service = new \App\Services\MyParcelService();
    
    // Fetch recent shipments from history
    echo "Fetching shipment history from MyParcel...\n";
    $history = $service->getShipmentHistory(1);
    print_r($history);
    exit;

    foreach ($history['data'] as $shipment) {
        $integrationId = $shipment['integration_order_id'] ?? null;
        $trackingNo = $shipment['tracking_no'] ?? null;
        $labelUrl = $shipment['label_url'] ?? $shipment['consignment_note'] ?? null;
        
        if ($integrationId && $trackingNo) {
            $order = \App\Models\Order::find($integrationId);
            if ($order && empty($order->tracking_no)) {
                $order->update([
                    'tracking_no' => $trackingNo,
                    'awb_label_url' => $labelUrl,
                    'status' => 'processing',
                ]);
                echo "Successfully synced tracking number for Order #{$order->order_number}: {$trackingNo}\n";
            }
        }
    }
    echo "Done syncing.\n";
    
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
