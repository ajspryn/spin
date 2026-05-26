<?php

/**
 * Patch vendor/laravel/framework/config/database.php to replace
 * the deprecated PDO::MYSQL_ATTR_SSL_CA constant (deprecated in PHP 8.5)
 * with the new Pdo\Mysql::ATTR_SSL_CA, falling back gracefully.
 *
 * This script is run automatically via composer post-install-cmd / post-update-cmd.
 */

$file = __DIR__ . '/../vendor/laravel/framework/config/database.php';

if (! file_exists($file)) {
     echo "patch-vendor-db: file not found, skipping.\n";
     exit(0);
}

$original = file_get_contents($file);

$old = 'PDO::MYSQL_ATTR_SSL_CA';
$new = "(defined('Pdo\\\\Mysql::ATTR_SSL_CA') ? Pdo\\Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA)";

if (str_contains($original, $new)) {
     echo "patch-vendor-db: already patched, skipping.\n";
     exit(0);
}

if (! str_contains($original, $old)) {
     echo "patch-vendor-db: target string not found, skipping.\n";
     exit(0);
}

$patched = str_replace($old, $new, $original);
file_put_contents($file, $patched);

echo "patch-vendor-db: patched {$file} successfully.\n";
