<?php

/**
 * Test Live API Endpoints
 * 
 * This script tests all the live API endpoints to ensure they work without errors
 * especially after fixing the status column issue in the loans table.
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "========================================\n";
echo "Testing Live API Endpoints\n";
echo "========================================\n\n";

// Helper function to make authenticated API requests
function testEndpoint($endpoint, $params = [], $token = null)
{
    $baseUrl = 'http://127.0.0.1:8888/marcci-dashboard/api/live';
    
    $url = $baseUrl . '/' . $endpoint;
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json',
        $token ? "Authorization: Bearer $token" : ''
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

// Get a test user token
echo "1. Getting authentication token...\n";
$user = \App\Models\User::whereNotNull('sacco_id')
    ->where('sacco_join_status', 'Approved')
    ->first();

if (!$user) {
    echo "❌ ERROR: No approved user found with sacco membership.\n";
    echo "Please ensure you have at least one user with sacco_join_status = 'Approved'\n\n";
    exit(1);
}

echo "✅ Using user: {$user->name} (ID: {$user->id}, Sacco: {$user->sacco_id})\n\n";

// Create a token for testing
$token = $user->createToken('test-token')->plainTextToken;
echo "✅ Token created successfully\n\n";

// Test 1: Dashboard Endpoint
echo "========================================\n";
echo "2. Testing Dashboard Endpoint\n";
echo "========================================\n";
$result = testEndpoint('dashboard', [], $token);
echo "HTTP Code: {$result['http_code']}\n";

if ($result['http_code'] == 200 && $result['response']['success']) {
    echo "✅ SUCCESS: Dashboard loaded\n";
    echo "Account Balance: " . ($result['response']['data']['account_balance'] ?? 'N/A') . "\n";
    echo "Total Transactions: " . ($result['response']['data']['total_transactions'] ?? 'N/A') . "\n";
    echo "Active Loans: " . ($result['response']['data']['loans_summary']['active_loans'] ?? 'N/A') . "\n";
} else {
    echo "❌ ERROR: Dashboard failed\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
}
echo "\n";

// Test 2: Loans Endpoint (Most Important - we fixed status issue here)
echo "========================================\n";
echo "3. Testing Loans Endpoint\n";
echo "========================================\n";
$result = testEndpoint('loans', ['page' => 1, 'per_page' => 5], $token);
echo "HTTP Code: {$result['http_code']}\n";

if ($result['http_code'] == 200 && $result['response']['success']) {
    echo "✅ SUCCESS: Loans loaded\n";
    $loansData = $result['response']['data'] ?? [];
    echo "Total Loans: " . ($result['response']['meta']['pagination']['total'] ?? 0) . "\n";
    echo "Current Page Loans: " . count($loansData) . "\n";
    
    if (!empty($loansData)) {
        $firstLoan = $loansData[0];
        echo "\nFirst Loan Details:\n";
        echo "  - ID: {$firstLoan['id']}\n";
        echo "  - Amount: {$firstLoan['amount']}\n";
        echo "  - Status: " . ($firstLoan['status'] ?? 'MISSING!') . "\n";
        echo "  - Is Fully Paid: " . ($firstLoan['is_fully_paid'] ?? 'N/A') . "\n";
        echo "  - Balance Calculated: " . ($firstLoan['balance_calculated'] ?? 'N/A') . "\n";
        echo "  - Is Overdue: " . ($firstLoan['is_overdue'] ? 'Yes' : 'No') . "\n";
        
        if (!isset($firstLoan['status'])) {
            echo "\n❌ WARNING: 'status' field is missing from loan data!\n";
            echo "The Loan model needs a status accessor.\n";
        } else {
            echo "\n✅ 'status' field is present!\n";
        }
    }
    
    echo "\nSummary Statistics:\n";
    $summary = $result['response']['meta']['summary'] ?? [];
    echo "  - Total Loans: " . ($summary['total_loans'] ?? 'N/A') . "\n";
    echo "  - Active Loans: " . ($summary['active_loans'] ?? 'N/A') . "\n";
    echo "  - Total Borrowed: " . ($summary['total_borrowed'] ?? 'N/A') . "\n";
    echo "  - Total Balance: " . ($summary['total_balance'] ?? 'N/A') . "\n";
} else {
    echo "❌ ERROR: Loans endpoint failed\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
}
echo "\n";

// Test 3: Loans with Overdue Filter
echo "========================================\n";
echo "4. Testing Loans with Overdue Filter\n";
echo "========================================\n";
$result = testEndpoint('loans', ['page' => 1, 'per_page' => 5, 'overdue' => 'true'], $token);
echo "HTTP Code: {$result['http_code']}\n";

if ($result['http_code'] == 200 && $result['response']['success']) {
    echo "✅ SUCCESS: Overdue filter works\n";
    $loansData = $result['response']['data'] ?? [];
    echo "Overdue Loans: " . count($loansData) . "\n";
} else {
    echo "❌ ERROR: Overdue filter failed\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
}
echo "\n";

// Test 4: Transactions Endpoint
echo "========================================\n";
echo "5. Testing Transactions Endpoint\n";
echo "========================================\n";
$result = testEndpoint('transactions', ['page' => 1, 'per_page' => 5], $token);
echo "HTTP Code: {$result['http_code']}\n";

if ($result['http_code'] == 200 && $result['response']['success']) {
    echo "✅ SUCCESS: Transactions loaded\n";
    $transData = $result['response']['data'] ?? [];
    echo "Total Transactions: " . ($result['response']['meta']['pagination']['total'] ?? 0) . "\n";
    echo "Current Page: " . count($transData) . "\n";
    
    if (!empty($transData)) {
        $firstTrans = $transData[0];
        echo "\nFirst Transaction:\n";
        echo "  - ID: {$firstTrans['id']}\n";
        echo "  - Type: {$firstTrans['type']}\n";
        echo "  - Amount: {$firstTrans['amount']}\n";
    }
} else {
    echo "❌ ERROR: Transactions endpoint failed\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
}
echo "\n";

// Test 5: Contribution Programs Endpoint
echo "========================================\n";
echo "6. Testing Contribution Programs Endpoint\n";
echo "========================================\n";
$result = testEndpoint('contribution-programs', ['page' => 1, 'per_page' => 5], $token);
echo "HTTP Code: {$result['http_code']}\n";

if ($result['http_code'] == 200 && $result['response']['success']) {
    echo "✅ SUCCESS: Contribution programs loaded\n";
    $programsData = $result['response']['data'] ?? [];
    echo "Total Programs: " . ($result['response']['meta']['pagination']['total'] ?? 0) . "\n";
    echo "Current Page: " . count($programsData) . "\n";
    
    if (!empty($programsData)) {
        $firstProg = $programsData[0];
        echo "\nFirst Program:\n";
        echo "  - ID: {$firstProg['id']}\n";
        echo "  - Name: {$firstProg['name']}\n";
        echo "  - Status: {$firstProg['status']}\n";
        echo "  - Payment Rate: {$firstProg['payment_rate']}%\n";
    }
} else {
    echo "❌ ERROR: Contribution programs endpoint failed\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
}
echo "\n";

// Test 6: Statistics Endpoint
echo "========================================\n";
echo "7. Testing Statistics Endpoint\n";
echo "========================================\n";
$result = testEndpoint('statistics', [], $token);
echo "HTTP Code: {$result['http_code']}\n";

if ($result['http_code'] == 200 && $result['response']['success']) {
    echo "✅ SUCCESS: Statistics loaded\n";
    $stats = $result['response']['data'] ?? [];
    echo "Statistics Available: " . count($stats) . " items\n";
} else {
    echo "❌ ERROR: Statistics endpoint failed\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
}
echo "\n";

// Clean up token
$user->tokens()->delete();

echo "========================================\n";
echo "Testing Complete!\n";
echo "========================================\n";
echo "\nKey Points:\n";
echo "1. All endpoints should return HTTP 200\n";
echo "2. Loans should have 'status' field (Active/Completed)\n";
echo "3. No SQL errors about missing 'status' column\n";
echo "4. Overdue filter should work correctly\n";
echo "\n";
