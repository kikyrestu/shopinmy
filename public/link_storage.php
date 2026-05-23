<?php
/**
 * NexShop - cPanel Storage Symlink Helper
 * 
 * Upload this file inside your public_html folder.
 * Run this from browser: https://yourdomain.com/link_storage.php
 */

$targetFolder = __DIR__ . '/../nexshop_core/storage/app/public';
$linkFolder = __DIR__ . '/storage';

echo "<h2>NexShop Storage Symlink Creator</h2>";

if (file_exists($linkFolder)) {
    echo "<p style='color:orange;'>Warning: The 'storage' folder already exists in public_html.</p>";
    echo "<p>Please delete it manually via cPanel File Manager first, then refresh this page.</p>";
} else {
    try {
        symlink($targetFolder, $linkFolder);
        echo "<p style='color:green;font-weight:bold;'>Success! The storage link has been created successfully.</p>";
        echo "<p>Your product images will now load correctly.</p>";
        echo "<p style='color:red;'>SECURITY WARNING: Please delete this file (link_storage.php) immediately from cPanel!</p>";
    } catch (\Exception $e) {
        echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
        echo "<p>Your host might have disabled the symlink() function. You may need to ask your hosting provider for help.</p>";
    }
}
