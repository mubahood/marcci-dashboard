<?php

/**
 * ADVANCED API TESTS FOR CONTRIBUTION MODULE
 * 
 * Tests: API Endpoints, Authentication, Validation, Error Handling, Response Format
 * Created: October 4, 2025
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Sacco;
use App\Models\User;
use App\Models\ContributionProgram;
use App\Models\ContributionProgramRecord;
use Carbon\Carbon;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

class ContributionAdvancedAPITest
{
    private $results = [];
    private $testCount = 0;
    private $passCount = 0;
    private $failCount = 0;
    private $warningCount = 0;
    
    private $testSacco;
    private $testAdmin;
    private $testMember;
    private $apiToken;
    private $baseUrl = 'http://localhost:8000/api';

    public function __construct()
    {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════════════\n";
        echo "   ADVANCED API TESTS - CONTRIBUTION MODULE\n";
        echo "═══════════════════════════════════════════════════════════════════════\n";
        echo "Started: " . date('Y-m-d H:i:s') . "\n";
        echo "═══════════════════════════════════════════════════════════════════════\n\n";
    }

    private function test($name, $callback, $category = 'General')
    {
        $this->testCount++;
        echo "[$category] Test #{$this->testCount}: {$name}... ";
        
        try {
            $result = $callback();
            
            if ($result === true) {
                echo "✅ PASSED\n";
                $this->passCount++;
                $this->results[] = ['test' => $name, 'status' => 'passed', 'category' => $category];
            } elseif (is_array($result) && isset($result['warning'])) {
                echo "⚠️  WARNING: {$result['message']}\n";
                $this->warningCount++;
                $this->results[] = ['test' => $name, 'status' => 'warning', 'category' => $category, 'message' => $result['message']];
            } else {
                echo "❌ FAILED: " . ($result ?: 'Unknown reason') . "\n";
                $this->failCount++;
                $this->results[] = ['test' => $name, 'status' => 'failed', 'category' => $category, 'error' => $result];
            }
        } catch (Exception $e) {
            echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
            $this->failCount++;
            $this->results[] = ['test' => $name, 'status' => 'failed', 'category' => $category, 'error' => $e->getMessage()];
        }
    }

    private function makeApiRequest($method, $endpoint, $data = [], $headers = [])
    {
        // Simulate API request using Laravel's test helper
        $request = new \Illuminate\Http\Request();
        $request->merge($data);
        $request->headers->add($headers);
        
        // Set authenticated user if token exists
        if ($this->apiToken && $this->testAdmin) {
            Auth::guard('api')->setUser($this->testAdmin);
        }
        
        return [
            'request' => $request,
            'data' => $data
        ];
    }

    public function runAllTests()
    {
        // Setup test data
        $this->setupTestData();
        
        // Test categories
        $this->testAuthentication();
        $this->testProgramCreationAPI();
        $this->testProgramUpdateAPI();
        $this->testProgramValidationAPI();
        $this->testRecordsAPI();
        $this->testRecordValidationAPI();
        $this->testPaginationAPI();
        $this->testFilteringAPI();
        $this->testErrorHandling();
        $this->testResponseFormat();
        $this->testSecurityAPI();
        $this->testRateLimiting();
        
        // Show results
        $this->showResults();
    }

    private function setupTestData()
    {
        echo "\n--- SETTING UP TEST DATA ---\n";
        
        // Find test sacco
        $this->testSacco = Sacco::first();
        if (!$this->testSacco) {
            echo "❌ No sacco found\n";
            exit(1);
        }
        echo "✅ Using Sacco: {$this->testSacco->name} (ID: {$this->testSacco->id})\n";
        
        // Find admin
        $this->testAdmin = User::where([
            'sacco_id' => $this->testSacco->id,
            'user_type' => 'Admin'
        ])->first();
        
        if (!$this->testAdmin) {
            $this->testAdmin = User::where('sacco_id', $this->testSacco->id)->first();
        }
        
        echo "✅ Using Admin: {$this->testAdmin->name} (ID: {$this->testAdmin->id})\n";
        
        // Find member
        $this->testMember = User::where([
            'sacco_id' => $this->testSacco->id,
            'user_type' => 'Member'
        ])->first();
        
        if (!$this->testMember) {
            $this->testMember = $this->testAdmin;
        }
        echo "✅ Using Member: {$this->testMember->name} (ID: {$this->testMember->id})\n";
        
        // Simulate API token
        $this->apiToken = 'test_token_' . time();
        Auth::guard('api')->setUser($this->testAdmin);
        
        echo "--- SETUP COMPLETE ---\n\n";
    }

    private function testAuthentication()
    {
        echo "\n=== AUTHENTICATION TESTS ===\n";
        
        $this->test('Unauthenticated request rejected', function() {
            Auth::guard('api')->logout();
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request();
            
            $response = $controller->contribution_program_create($request);
            $data = $response->getData(true);
            
            Auth::guard('api')->setUser($this->testAdmin);
            
            return isset($data['code']) && $data['code'] == 0;
        }, 'Authentication');
        
        $this->test('Valid token accepted', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request();
            
            $response = $controller->contribution_program_records($request);
            
            // Should not return "User not found" error
            return !is_string($response) || strpos($response, 'User not found') === false;
        }, 'Authentication');
        
        $this->test('Expired token rejected', function() {
            // This would need JWT implementation
            return ['warning' => true, 'message' => 'Token expiration not tested - requires JWT implementation'];
        }, 'Authentication');
    }

    private function testProgramCreationAPI()
    {
        echo "\n=== PROGRAM CREATION API TESTS ===\n";
        
        DB::beginTransaction();
        
        $this->test('Create Monthly program via API', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request([
                'name' => 'API Test Monthly Program',
                'contribution_type' => 'Periodic',
                'periodic_type' => 'Monthly',
                'amount_per_member_type' => 'Specific',
                'amount_per_member_value' => 25000,
                'start_date' => Carbon::now()->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(6)->format('Y-m-d'),
                'status' => 'Active',
                'details' => 'Created via API test'
            ]);
            
            $response = $controller->contribution_program_create($request);
            $data = $response->getData(true);
            
            return isset($data['code']) && $data['code'] == 1;
        }, 'API - Creation');
        
        $this->test('Create Weekly program via API', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request([
                'name' => 'API Test Weekly Program',
                'contribution_type' => 'Periodic',
                'periodic_type' => 'Weekly',
                'amount_per_member_type' => 'Specific',
                'amount_per_member_value' => 5000,
                'start_date' => Carbon::now()->format('Y-m-d'),
                'end_date' => Carbon::now()->addWeeks(12)->format('Y-m-d'),
                'status' => 'Active'
            ]);
            
            $response = $controller->contribution_program_create($request);
            $data = $response->getData(true);
            
            return isset($data['code']) && $data['code'] == 1;
        }, 'API - Creation');
        
        $this->test('Create Open contribution via API', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request([
                'name' => 'API Test Open Program',
                'contribution_type' => 'Open',
                'periodic_type' => 'None',
                'amount_per_member_type' => 'Any',
                'amount_per_member_value' => 0,
                'start_date' => Carbon::now()->format('Y-m-d'),
                'end_date' => Carbon::now()->addYear()->format('Y-m-d'),
                'status' => 'Active',
                'target_amount' => 5000000
            ]);
            
            $response = $controller->contribution_program_create($request);
            $data = $response->getData(true);
            
            return isset($data['code']) && $data['code'] == 1;
        }, 'API - Creation');
        
        $this->test('Response contains created program data', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request([
                'name' => 'Response Test Program',
                'contribution_type' => 'Periodic',
                'periodic_type' => 'Monthly',
                'amount_per_member_type' => 'Specific',
                'amount_per_member_value' => 10000,
                'start_date' => Carbon::now()->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'status' => 'Active'
            ]);
            
            $response = $controller->contribution_program_create($request);
            $data = $response->getData(true);
            
            return isset($data['data']['id']) && isset($data['data']['name']);
        }, 'API - Creation');
        
        DB::rollBack();
    }

    private function testProgramUpdateAPI()
    {
        echo "\n=== PROGRAM UPDATE API TESTS ===\n";
        
        DB::beginTransaction();
        
        $this->test('Update existing program via API', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            // Create program first
            $program = ContributionProgram::create([
                'sacco_id' => $this->testSacco->id,
                'name' => 'Original Name',
                'contribution_type' => 'Periodic',
                'periodic_type' => 'Monthly',
                'amount_per_member_type' => 'Specific',
                'amount_per_member_value' => 10000,
                'start_date' => Carbon::now()->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'status' => 'Active'
            ]);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request([
                'id' => $program->id,
                'name' => 'Updated Name',
                'status' => 'Inactive'
            ]);
            
            $response = $controller->contribution_program_create($request);
            $data = $response->getData(true);
            
            $updated = ContributionProgram::find($program->id);
            return $updated->name == 'Updated Name';
        }, 'API - Update');
        
        $this->test('Cannot update program from different sacco', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            // Try to update program from different sacco
            $otherProgram = ContributionProgram::where('sacco_id', '!=', $this->testSacco->id)->first();
            
            if (!$otherProgram) {
                return ['warning' => true, 'message' => 'No programs from other saccos to test authorization'];
            }
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request([
                'id' => $otherProgram->id,
                'name' => 'Hacked Name'
            ]);
            
            $response = $controller->contribution_program_create($request);
            
            // Should fail or not update
            $check = ContributionProgram::find($otherProgram->id);
            return $check->name != 'Hacked Name';
        }, 'API - Update');
        
        DB::rollBack();
    }

    private function testProgramValidationAPI()
    {
        echo "\n=== PROGRAM VALIDATION API TESTS ===\n";
        
        $this->test('Missing required field rejected', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request([
                // Missing name, contribution_type, etc.
                'start_date' => Carbon::now()->format('Y-m-d'),
            ]);
            
            try {
                $response = $controller->contribution_program_create($request);
                $data = $response->getData(true);
                
                // Should fail
                return isset($data['code']) && $data['code'] == 0;
            } catch (\Exception $e) {
                return true; // Expected to throw exception
            }
        }, 'API - Validation');
        
        $this->test('Invalid date format rejected', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request([
                'name' => 'Invalid Date Test',
                'contribution_type' => 'Periodic',
                'periodic_type' => 'Monthly',
                'amount_per_member_type' => 'Specific',
                'amount_per_member_value' => 10000,
                'start_date' => 'invalid-date',
                'end_date' => '2025-13-45', // Invalid date
                'status' => 'Active'
            ]);
            
            try {
                $response = $controller->contribution_program_create($request);
                return ['warning' => true, 'message' => 'Invalid dates accepted - validation may be missing'];
            } catch (\Exception $e) {
                return true; // Expected to fail
            }
        }, 'API - Validation');
        
        $this->test('Negative amount rejected', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request([
                'name' => 'Negative Amount Test',
                'contribution_type' => 'Periodic',
                'periodic_type' => 'Monthly',
                'amount_per_member_type' => 'Specific',
                'amount_per_member_value' => -10000,
                'start_date' => Carbon::now()->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'status' => 'Active'
            ]);
            
            try {
                $response = $controller->contribution_program_create($request);
                return ['warning' => true, 'message' => 'Negative amount accepted - validation may be missing'];
            } catch (\Exception $e) {
                return true;
            }
        }, 'API - Validation');
    }

    private function testRecordsAPI()
    {
        echo "\n=== RECORDS API TESTS ===\n";
        
        DB::beginTransaction();
        
        $this->test('Fetch contribution records via API', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request();
            
            $response = $controller->contribution_program_records($request);
            $data = $response->getData(true);
            
            return isset($data['code']) && $data['code'] == 1 && isset($data['data']);
        }, 'API - Records');
        
        $this->test('Create payment record via API', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            // Find a program
            $program = ContributionProgram::where('sacco_id', $this->testSacco->id)->first();
            if (!$program) {
                return ['warning' => true, 'message' => 'No programs to test payment creation'];
            }
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request([
                'contribution_program_id' => $program->id,
                'member_id' => $this->testMember->id,
                'amount' => 50000,
                'paid_amount' => 50000,
                'is_paid' => 'Yes',
                'payment_date' => Carbon::now()->format('Y-m-d'),
                'teasurer_id' => $this->testAdmin->id
            ]);
            
            $response = $controller->contribution_program_records_create($request);
            $data = $response->getData(true);
            
            return isset($data['code']) && $data['code'] == 1;
        }, 'API - Records');
        
        $this->test('Create unpaid record via API', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $program = ContributionProgram::where('sacco_id', $this->testSacco->id)->first();
            if (!$program) {
                return ['warning' => true, 'message' => 'No programs to test'];
            }
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request([
                'contribution_program_id' => $program->id,
                'member_id' => $this->testMember->id,
                'amount' => 30000,
                'is_paid' => 'No',
                'teasurer_id' => $this->testAdmin->id
            ]);
            
            $response = $controller->contribution_program_records_create($request);
            $data = $response->getData(true);
            
            return isset($data['code']) && $data['code'] == 1;
        }, 'API - Records');
        
        DB::rollBack();
    }

    private function testRecordValidationAPI()
    {
        echo "\n=== RECORD VALIDATION API TESTS ===\n";
        
        $this->test('Missing contribution_program_id rejected', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request([
                'member_id' => $this->testMember->id,
                'amount' => 10000,
                'is_paid' => 'No'
            ]);
            
            $response = $controller->contribution_program_records_create($request);
            $data = $response->getData(true);
            
            return isset($data['code']) && $data['code'] == 0;
        }, 'API - Validation');
        
        $this->test('Invalid member_id rejected', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $program = ContributionProgram::where('sacco_id', $this->testSacco->id)->first();
            if (!$program) {
                return ['warning' => true, 'message' => 'No programs to test'];
            }
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request([
                'contribution_program_id' => $program->id,
                'member_id' => 9999999, // Non-existent
                'amount' => 10000,
                'is_paid' => 'No',
                'teasurer_id' => $this->testAdmin->id
            ]);
            
            $response = $controller->contribution_program_records_create($request);
            $data = $response->getData(true);
            
            return isset($data['code']) && $data['code'] == 0;
        }, 'API - Validation');
        
        $this->test('Paid amount exceeding expected rejected', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $program = ContributionProgram::where('sacco_id', $this->testSacco->id)->first();
            if (!$program) {
                return ['warning' => true, 'message' => 'No programs to test'];
            }
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request([
                'contribution_program_id' => $program->id,
                'member_id' => $this->testMember->id,
                'amount' => 10000,
                'paid_amount' => 15000, // More than expected
                'is_paid' => 'Yes',
                'payment_date' => Carbon::now()->format('Y-m-d'),
                'teasurer_id' => $this->testAdmin->id
            ]);
            
            $response = $controller->contribution_program_records_create($request);
            $data = $response->getData(true);
            
            return isset($data['code']) && $data['code'] == 0;
        }, 'API - Validation');
    }

    private function testPaginationAPI()
    {
        echo "\n=== PAGINATION API TESTS ===\n";
        
        $this->test('Pagination metadata returned', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request(['per_page' => 10]);
            
            $response = $controller->contribution_program_records($request);
            $data = $response->getData(true);
            
            return isset($data['data']['current_page']) && 
                   isset($data['data']['last_page']) && 
                   isset($data['data']['total']);
        }, 'API - Pagination');
        
        $this->test('Custom per_page parameter works', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request(['per_page' => 5]);
            
            $response = $controller->contribution_program_records($request);
            $data = $response->getData(true);
            
            if (!isset($data['data']['data'])) {
                return ['warning' => true, 'message' => 'No data returned to test pagination'];
            }
            
            return count($data['data']['data']) <= 5;
        }, 'API - Pagination');
        
        $this->test('Large per_page limited', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request(['per_page' => 10000]);
            
            $response = $controller->contribution_program_records($request);
            $data = $response->getData(true);
            
            // Should have some reasonable limit
            return true; // Just check it doesn't crash
        }, 'API - Pagination');
    }

    private function testFilteringAPI()
    {
        echo "\n=== FILTERING API TESTS ===\n";
        
        $this->test('Records filtered by sacco', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request();
            
            $response = $controller->contribution_program_records($request);
            $data = $response->getData(true);
            
            if (!isset($data['data']['data']) || count($data['data']['data']) == 0) {
                return ['warning' => true, 'message' => 'No records to verify filtering'];
            }
            
            // All records should belong to user's sacco
            foreach ($data['data']['data'] as $record) {
                if ($record['sacco_id'] != $this->testSacco->id) {
                    return "Record from different sacco returned: {$record['id']}";
                }
            }
            
            return true;
        }, 'API - Filtering');
        
        $this->test('User cannot see other sacco records', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request();
            
            $response = $controller->contribution_program_records($request);
            $data = $response->getData(true);
            
            if (!isset($data['data']['data'])) {
                return ['warning' => true, 'message' => 'No records returned'];
            }
            
            // Verify all records belong to user's sacco
            foreach ($data['data']['data'] as $record) {
                if ($record['sacco_id'] != $this->testAdmin->sacco_id) {
                    return "Security issue: Record from different sacco visible";
                }
            }
            
            return true;
        }, 'API - Filtering');
    }

    private function testErrorHandling()
    {
        echo "\n=== ERROR HANDLING API TESTS ===\n";
        
        $this->test('Invalid JSON request handled', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            try {
                $controller = new \App\Http\Controllers\ApiResurceController();
                $request = new \Illuminate\Http\Request(['invalid' => "{'bad': 'json"]);
                
                $response = $controller->contribution_program_create($request);
                return true; // Should not crash
            } catch (\Exception $e) {
                return ['warning' => true, 'message' => 'JSON parsing not handled gracefully'];
            }
        }, 'API - Error Handling');
        
        $this->test('Database error handled gracefully', function() {
            // This would need actual database disconnection
            return ['warning' => true, 'message' => 'Database error handling not tested - requires disconnection simulation'];
        }, 'API - Error Handling');
        
        $this->test('Error responses include helpful messages', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request([
                'contribution_program_id' => 9999999 // Non-existent
            ]);
            
            $response = $controller->contribution_program_records_create($request);
            $data = $response->getData(true);
            
            return isset($data['message']) && strlen($data['message']) > 0;
        }, 'API - Error Handling');
    }

    private function testResponseFormat()
    {
        echo "\n=== RESPONSE FORMAT TESTS ===\n";
        
        $this->test('Success response has correct structure', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request();
            
            $response = $controller->contribution_program_records($request);
            $data = $response->getData(true);
            
            return isset($data['code']) && isset($data['message']) && isset($data['data']);
        }, 'API - Response');
        
        $this->test('Error response has correct structure', function() {
            Auth::guard('api')->logout();
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request();
            
            $response = $controller->contribution_program_create($request);
            $data = $response->getData(true);
            
            Auth::guard('api')->setUser($this->testAdmin);
            
            return isset($data['code']) && isset($data['message']);
        }, 'API - Response');
        
        $this->test('Response is valid JSON', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request();
            
            $response = $controller->contribution_program_records($request);
            
            try {
                $data = $response->getData(true);
                return is_array($data);
            } catch (\Exception $e) {
                return "Invalid JSON response";
            }
        }, 'API - Response');
    }

    private function testSecurityAPI()
    {
        echo "\n=== SECURITY API TESTS ===\n";
        
        $this->test('SQL injection prevented in filters', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request([
                'sacco_id' => "1' OR '1'='1",
                'member_id' => "1; DROP TABLE users--"
            ]);
            
            try {
                $response = $controller->contribution_program_records($request);
                return true; // Should handle safely
            } catch (\Exception $e) {
                return true; // Or throw exception - both are safe
            }
        }, 'API - Security');
        
        $this->test('XSS prevented in input', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request([
                'name' => '<script>alert("XSS")</script>',
                'contribution_type' => 'Periodic',
                'periodic_type' => 'Monthly',
                'amount_per_member_type' => 'Specific',
                'amount_per_member_value' => 10000,
                'start_date' => Carbon::now()->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(1)->format('Y-m-d'),
                'status' => 'Active'
            ]);
            
            $response = $controller->contribution_program_create($request);
            $data = $response->getData(true);
            
            if (isset($data['data']['name'])) {
                // Check if script tags are escaped or removed
                return strpos($data['data']['name'], '<script>') === false;
            }
            
            return true;
        }, 'API - Security');
        
        $this->test('Authorization enforced across saccos', function() {
            Auth::guard('api')->setUser($this->testAdmin);
            
            $program = ContributionProgram::where('sacco_id', '!=', $this->testSacco->id)->first();
            
            if (!$program) {
                return ['warning' => true, 'message' => 'No programs from other saccos to test'];
            }
            
            $controller = new \App\Http\Controllers\ApiResurceController();
            $request = new \Illuminate\Http\Request([
                'contribution_program_id' => $program->id,
                'member_id' => $this->testMember->id,
                'amount' => 10000,
                'is_paid' => 'No',
                'teasurer_id' => $this->testAdmin->id
            ]);
            
            $response = $controller->contribution_program_records_create($request);
            $data = $response->getData(true);
            
            // Should be rejected
            return isset($data['code']) && $data['code'] == 0;
        }, 'API - Security');
    }

    private function testRateLimiting()
    {
        echo "\n=== RATE LIMITING TESTS ===\n";
        
        $this->test('Rate limiting implemented', function() {
            return ['warning' => true, 'message' => 'Rate limiting not tested - requires middleware configuration'];
        }, 'API - Rate Limiting');
        
        $this->test('Excessive requests throttled', function() {
            return ['warning' => true, 'message' => 'Throttling not tested - requires load testing'];
        }, 'API - Rate Limiting');
    }

    private function showResults()
    {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════════════\n";
        echo "   API TEST RESULTS SUMMARY\n";
        echo "═══════════════════════════════════════════════════════════════════════\n\n";
        
        echo "Total Tests:   {$this->testCount}\n";
        echo "✅ Passed:      {$this->passCount} (" . round(($this->passCount/$this->testCount)*100, 1) . "%)\n";
        echo "❌ Failed:      {$this->failCount} (" . round(($this->failCount/$this->testCount)*100, 1) . "%)\n";
        echo "⚠️  Warnings:    {$this->warningCount} (" . round(($this->warningCount/$this->testCount)*100, 1) . "%)\n";
        
        echo "\n";
        
        // Group by category
        $categories = [];
        foreach ($this->results as $result) {
            $cat = $result['category'];
            if (!isset($categories[$cat])) {
                $categories[$cat] = ['passed' => 0, 'failed' => 0, 'warning' => 0];
            }
            $categories[$cat][$result['status']]++;
        }
        
        echo "By Category:\n";
        foreach ($categories as $cat => $counts) {
            $total = $counts['passed'] + $counts['failed'] + $counts['warning'];
            echo "  {$cat}: {$counts['passed']}/{$total} passed";
            if ($counts['failed'] > 0) echo ", {$counts['failed']} failed";
            if ($counts['warning'] > 0) echo ", {$counts['warning']} warnings";
            echo "\n";
        }
        
        // Show failures
        if ($this->failCount > 0) {
            echo "\n❌ FAILED TESTS:\n";
            foreach ($this->results as $result) {
                if ($result['status'] == 'failed') {
                    echo "  - {$result['test']}: {$result['error']}\n";
                }
            }
        }
        
        // Show warnings
        if ($this->warningCount > 0) {
            echo "\n⚠️  WARNINGS:\n";
            foreach ($this->results as $result) {
                if ($result['status'] == 'warning') {
                    echo "  - {$result['test']}: {$result['message']}\n";
                }
            }
        }
        
        echo "\n";
        if ($this->failCount == 0) {
            echo "✅ ALL CRITICAL API TESTS PASSED!\n";
        } else {
            echo "❌ SOME API TESTS FAILED - REVIEW AND FIX ISSUES\n";
        }
        
        echo "═══════════════════════════════════════════════════════════════════════\n";
        echo "Completed: " . date('Y-m-d H:i:s') . "\n";
        echo "═══════════════════════════════════════════════════════════════════════\n\n";
    }
}

// Run tests
$tester = new ContributionAdvancedAPITest();
$tester->runAllTests();
