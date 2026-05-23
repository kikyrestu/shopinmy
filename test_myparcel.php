<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$svc = app(App\Services\MyParcelService::class);
try {
    $res = $svc->checkPrice('50000', '50200', 1.0);
    echo json_encode($res, JSON_PRETTY_PRINT);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
