<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "Users Table Structure\n";
echo "========================================\n\n";

// Get all columns from users table
$columns = Schema::getColumnListing('users');

echo "Total Columns: " . count($columns) . "\n\n";
echo "Column Names:\n";
echo "-------------------\n";

foreach ($columns as $column) {
    echo "- $column\n";
}

echo "\n========================================\n";
echo "Sample User Data (First Record)\n";
echo "========================================\n\n";

$user = DB::table('users')->first();
if ($user) {
    foreach ($user as $key => $value) {
        $displayValue = is_null($value) ? 'NULL' : (strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value);
        echo sprintf("%-30s : %s\n", $key, $displayValue);
    }
} else {
    echo "No users found in database\n";
}

echo "\n";
