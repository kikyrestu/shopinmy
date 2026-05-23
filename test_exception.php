<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    (new App\Services\MyParcelService())->generateAwbForOrder(App\Models\Order::find(1));
} catch (\Exception $e) {
    echo $e->getMessage();
}
