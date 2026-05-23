<?php
/**
 * NexShop - cPanel Database Migration Helper
 * 
 * Upload this file inside your public_html folder.
 * Run this from browser: https://yourdomain.com/migrate.php
 */

echo "<h2>NexShop Database Migrator</h2>";

// Adjust this path if you name your core folder differently
$corePath = __DIR__ . '/../nexshop_core';

if (!file_exists($corePath . '/artisan')) {
    echo "<p style='color:red;'>Error: Could not find the Laravel core folder.</p>";
    exit;
}

require $corePath . '/vendor/autoload.php';
$app = require_once $corePath . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

try {
    // --force is required in production environment
    $kernel->call('migrate', ['--force' => true, '--seed' => true]);
    echo "<p style='color:green;font-weight:bold;'>Success! Database migration completed.</p>";
    echo "<pre style='background:#f4f4f4;padding:10px;border:1px solid #ccc;'>" . $kernel->output() . "</pre>";
    
    echo "<p style='color:red;'>SECURITY WARNING: Please delete this file (migrate.php) immediately from cPanel!</p>";
} catch (\Exception $e) {
    echo "<p style='color:red;'>Error running migration: " . $e->getMessage() . "</p>";
}
