<?php
/**
 * NexShop - cPanel Cache Clear Helper
 * 
 * Upload this file inside your public_html folder.
 * Run this from browser: https://yourdomain.com/clear_cache.php
 */

echo "<h2>NexShop Cache Cleaner</h2>";

// Adjust this path if you name your core folder differently
$corePath = __DIR__ . '/../nexshop_core';

if (!file_exists($corePath . '/artisan')) {
    echo "<p style='color:red;'>Error: Could not find the Laravel core folder.</p>";
    echo "<p>Please ensure your system files are uploaded to: <b>" . realpath(__DIR__ . '/../') . "/nexshop_core</b></p>";
    exit;
}

require $corePath . '/vendor/autoload.php';
$app = require_once $corePath . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

try {
    $kernel->call('optimize:clear');
    echo "<p style='color:green;font-weight:bold;'>Success! All caches (views, config, routes, application) have been cleared.</p>";
    echo "<pre>" . $kernel->output() . "</pre>";
    
    echo "<p style='color:red;'>SECURITY WARNING: Please delete this file (clear_cache.php) immediately from cPanel!</p>";
} catch (\Exception $e) {
    echo "<p style='color:red;'>Error clearing cache: " . $e->getMessage() . "</p>";
}
