<?php

/**
 * Simple Test for Loan Model Status Field
 * 
 * This script verifies that the Loan model properly returns the 'status' field
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "========================================\n";
echo "Testing Loan Model Status Field\n";
echo "========================================\n\n";

// Get a sample loan
echo "1. Fetching sample loans...\n";
$loans = \App\Models\Loan::limit(5)->get();

if ($loans->isEmpty()) {
    echo "❌ No loans found in database\n";
    echo "Please create at least one loan to test\n\n";
    exit(1);
}

echo "✅ Found {$loans->count()} loans\n\n";

echo "========================================\n";
echo "2. Testing Loan Status Field\n";
echo "========================================\n\n";

$hasError = false;

foreach ($loans as $index => $loan) {
    echo "Loan #" . ($index + 1) . " (ID: {$loan->id}):\n";
    echo "  - Amount: {$loan->amount}\n";
    echo "  - Balance: {$loan->balance}\n";
    echo "  - Is Fully Paid: {$loan->is_fully_paid}\n";
    
    // Check if status field exists
    try {
        $status = $loan->status;
        echo "  - Status: {$status}\n";
        
        // Verify status is correct based on is_fully_paid
        if ($loan->is_fully_paid === 'Yes' && $status !== 'Completed') {
            echo "  ❌ ERROR: Status should be 'Completed' but got '{$status}'\n";
            $hasError = true;
        } elseif ($loan->is_fully_paid !== 'Yes' && $status !== 'Active') {
            echo "  ❌ ERROR: Status should be 'Active' but got '{$status}'\n";
            $hasError = true;
        } else {
            echo "  ✅ Status is correct!\n";
        }
    } catch (\Exception $e) {
        echo "  ❌ ERROR: Could not access status field\n";
        echo "  Error: {$e->getMessage()}\n";
        $hasError = true;
    }
    
    echo "\n";
}

echo "========================================\n";
echo "3. Testing Loan toArray() Method\n";
echo "========================================\n\n";

$firstLoan = $loans->first();
$loanArray = $firstLoan->toArray();

echo "Checking if 'status' is in array...\n";
if (isset($loanArray['status'])) {
    echo "✅ SUCCESS: 'status' field is present in array\n";
    echo "   Value: {$loanArray['status']}\n";
} else {
    echo "❌ ERROR: 'status' field is missing from array\n";
    echo "   Available fields: " . implode(', ', array_keys($loanArray)) . "\n";
    $hasError = true;
}

echo "\n";

echo "========================================\n";
echo "4. Testing Loan toJson() Method\n";
echo "========================================\n\n";

$loanJson = $firstLoan->toJson();
$loanDecoded = json_decode($loanJson, true);

echo "Checking if 'status' is in JSON...\n";
if (isset($loanDecoded['status'])) {
    echo "✅ SUCCESS: 'status' field is present in JSON\n";
    echo "   Value: {$loanDecoded['status']}\n";
} else {
    echo "❌ ERROR: 'status' field is missing from JSON\n";
    $hasError = true;
}

echo "\n";

echo "========================================\n";
echo "5. Testing Query with Status\n";
echo "========================================\n\n";

try {
    // Test that we can query and get status
    $testQuery = \App\Models\Loan::where('is_fully_paid', '!=', 'Yes')
        ->limit(3)
        ->get();
    
    echo "✅ Query executed successfully\n";
    echo "Active loans found: {$testQuery->count()}\n\n";
    
    foreach ($testQuery as $loan) {
        echo "Loan ID {$loan->id}:\n";
        echo "  - is_fully_paid: {$loan->is_fully_paid}\n";
        echo "  - status: {$loan->status}\n";
        
        if ($loan->status !== 'Active') {
            echo "  ❌ ERROR: Expected 'Active' but got '{$loan->status}'\n";
            $hasError = true;
        } else {
            echo "  ✅ Correct!\n";
        }
        echo "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: Query failed\n";
    echo "Error: {$e->getMessage()}\n";
    $hasError = true;
}

echo "========================================\n";
echo "Summary\n";
echo "========================================\n\n";

if ($hasError) {
    echo "❌ FAILED: Some tests did not pass\n";
    echo "\nPlease check:\n";
    echo "1. The Loan model has getStatusAttribute() method\n";
    echo "2. The \$appends array includes 'status'\n";
    echo "3. The logic correctly converts is_fully_paid to status\n";
    exit(1);
} else {
    echo "✅ SUCCESS: All tests passed!\n";
    echo "\nThe Loan model correctly:\n";
    echo "1. Has a 'status' accessor\n";
    echo "2. Includes 'status' in arrays and JSON\n";
    echo "3. Returns 'Active' for unpaid loans\n";
    echo "4. Returns 'Completed' for fully paid loans\n";
}

echo "\n";
