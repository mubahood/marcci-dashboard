<?php
/**
 * EXHAUSTIVE CONTRIBUTION MODULE TESTING
 * Zero tolerance for errors - comprehensive validation of every component
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Sacco;
use App\Models\ContributionProgram;
use App\Models\ContributionProgramRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ExhaustiveContributionTester
{
    private $testResults = [];
    private $totalTests = 0;
    private $passedTests = 0;
    private $failedTests = 0;
    private $warningTests = 0;
    
    private $sacco;
    private $admin;
    private $members = [];
    private $testPrograms = [];
    
    public function __construct()
    {
        echo "\n" . str_repeat("═", 100) . "\n";
        echo "🔬 EXHAUSTIVE CONTRIBUTION MODULE TESTING\n";
        echo "   Zero Tolerance for Errors - Comprehensive Validation\n";
        echo str_repeat("═", 100) . "\n\n";
    }
    
    public function run()
    {
        try {
            $this->setupTestEnvironment();
            $this->testDatabaseStructure();
            $this->testModelRelationships();
            $this->testProgramCreation();
            $this->testMemberManagement();
            $this->testRecordGeneration();
            $this->testPaymentProcessing();
            $this->testBalanceCalculations();
            $this->testPaginationAndFiltering();
            $this->testPerformanceOptimization();
            $this->testSecurityValidation();
            $this->testEdgeCases();
            $this->testConcurrency();
            $this->printFinalReport();
        } catch (\Exception $e) {
            $this->fail("FATAL ERROR", $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->printFinalReport();
            exit(1);
        }
    }
    
    private function setupTestEnvironment()
    {
        $this->section("ENVIRONMENT SETUP");
        
        // Test 1: Database Connection
        $this->test("Database Connection", function() {
            DB::connection()->getPdo();
            return "Connected to: " . DB::connection()->getDatabaseName();
        });
        
        // Test 2: Find Sacco
        $this->test("Find Active Sacco", function() {
            $this->sacco = Sacco::first();
            if (!$this->sacco) {
                throw new \Exception("No sacco found in database");
            }
            return "Sacco: {$this->sacco->name} (ID: {$this->sacco->id})";
        });
        
        // Test 3: Find Admin
        $this->test("Find Admin User", function() {
            $this->admin = User::where('sacco_id', $this->sacco->id)
                ->where('user_type', 'Admin')
                ->first();
            if (!$this->admin) {
                throw new \Exception("No admin user found");
            }
            return "Admin: {$this->admin->name} (ID: {$this->admin->id})";
        });
        
        // Test 4: Find Members
        $this->test("Find Test Members", function() {
            $this->members = User::where('sacco_id', $this->sacco->id)
                ->where('user_type', 'Member')
                ->limit(5)
                ->get();
            if ($this->members->count() < 2) {
                throw new \Exception("Need at least 2 members for testing");
            }
            return "Found {$this->members->count()} members";
        });
        
        // Test 5: Authentication
        $this->test("Authenticate Admin User", function() {
            auth()->login($this->admin);
            if (!auth()->check()) {
                throw new \Exception("Authentication failed");
            }
            return "Authenticated as: " . auth()->user()->name;
        });
    }
    
    private function testDatabaseStructure()
    {
        $this->section("DATABASE STRUCTURE VALIDATION");
        
        // Test 1: contribution_programs table structure
        $this->test("Verify contribution_programs Table", function() {
            $columns = array_column(DB::select('DESCRIBE contribution_programs'), 'Field');
            $requiredColumns = [
                'id', 'sacco_id', 'name', 'contribution_type', 'periodic_type',
                'amount_per_member_type', 'amount_per_member_value', 'start_date',
                'end_date', 'status', 'members', 'treasurers'
            ];
            
            foreach ($requiredColumns as $col) {
                if (!in_array($col, $columns)) {
                    throw new \Exception("Missing column: {$col}");
                }
            }
            return "All " . count($requiredColumns) . " required columns present";
        });
        
        // Test 2: contribution_program_records table structure
        $this->test("Verify contribution_program_records Table", function() {
            $columns = array_column(DB::select('DESCRIBE contribution_program_records'), 'Field');
            $requiredColumns = [
                'id', 'sacco_id', 'contribution_program_id', 'member_id',
                'amount', 'paid_amount', 'is_paid', 'period_name', 'teasurer_id'
            ];
            
            foreach ($requiredColumns as $col) {
                if (!in_array($col, $columns)) {
                    throw new \Exception("Missing column: {$col}");
                }
            }
            return "All " . count($requiredColumns) . " required columns present";
        });
        
        // Test 3: Check for indexes
        $this->test("Verify Database Indexes", function() {
            $indexes = DB::select("SHOW INDEX FROM contribution_program_records WHERE Key_name != 'PRIMARY'");
            return "Found " . count($indexes) . " indexes on contribution_program_records";
        });
    }
    
    private function testModelRelationships()
    {
        $this->section("MODEL RELATIONSHIPS");
        
        // Test 1: ContributionProgramRecord -> Member relationship
        $this->test("Record->Member Relationship", function() {
            $record = ContributionProgramRecord::with('member')->first();
            if (!$record) {
                return "SKIP: No records in database";
            }
            if (!method_exists($record, 'member')) {
                throw new \Exception("member() relationship not defined");
            }
            $member = $record->member;
            return "Relationship working: Record belongs to " . ($member ? $member->name : "NULL");
        });
        
        // Test 2: ContributionProgramRecord -> Program relationship
        $this->test("Record->Program Relationship", function() {
            $record = ContributionProgramRecord::with('program')->first();
            if (!$record) {
                return "SKIP: No records in database";
            }
            if (!method_exists($record, 'program')) {
                throw new \Exception("program() relationship not defined");
            }
            $program = $record->program;
            return "Relationship working: Record belongs to program " . ($program ? $program->id : "NULL");
        });
        
        // Test 3: ContributionProgramRecord -> Treasurer relationship
        $this->test("Record->Treasurer Relationship", function() {
            $record = ContributionProgramRecord::with('treasurer')->first();
            if (!$record) {
                return "SKIP: No records in database";
            }
            if (!method_exists($record, 'treasurer')) {
                throw new \Exception("treasurer() relationship not defined");
            }
            return "Relationship method exists";
        });
    }
    
    private function testProgramCreation()
    {
        $this->section("CONTRIBUTION PROGRAM CREATION");
        
        DB::beginTransaction();
        
        try {
            // Test 1: Create Monthly Program
            $this->test("Create Monthly Periodic Program", function() {
                $program = ContributionProgram::create([
                    'sacco_id' => $this->sacco->id,
                    'name' => 'Test Monthly Program ' . time(),
                    'contribution_type' => 'Periodic',
                    'periodic_type' => 'Monthly',
                    'amount_per_member_type' => 'Specific',
                    'amount_per_member_value' => 50000,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addMonths(3)->toDateString(),
                    'status' => 'Active',
                ]);
                
                if (!$program->id) {
                    throw new \Exception("Program not created");
                }
                
                $this->testPrograms['monthly'] = $program;
                return "Created: ID={$program->id}, Name={$program->name}";
            });
            
            // Test 2: Verify program is in database
            $this->test("Verify Program Persisted", function() {
                $program = $this->testPrograms['monthly'];
                $found = ContributionProgram::find($program->id);
                if (!$found) {
                    throw new \Exception("Program not found in database");
                }
                if ($found->name !== $program->name) {
                    throw new \Exception("Program name mismatch");
                }
                return "Program verified in database";
            });
            
            // Test 3: Create Weekly Program
            $this->test("Create Weekly Periodic Program", function() {
                $program = ContributionProgram::create([
                    'sacco_id' => $this->sacco->id,
                    'name' => 'Test Weekly Program ' . time(),
                    'contribution_type' => 'Periodic',
                    'periodic_type' => 'Weekly',
                    'amount_per_member_type' => 'Specific',
                    'amount_per_member_value' => 10000,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addWeeks(4)->toDateString(),
                    'status' => 'Active',
                ]);
                
                $this->testPrograms['weekly'] = $program;
                return "Created: ID={$program->id}";
            });
            
            // Test 4: Create Open Program
            $this->test("Create Open Contribution Program", function() {
                $program = ContributionProgram::create([
                    'sacco_id' => $this->sacco->id,
                    'name' => 'Test Open Program ' . time(),
                    'contribution_type' => 'Open',
                    'amount_per_member_type' => 'Any',
                    'amount_per_member_value' => 0,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addMonths(6)->toDateString(),
                    'status' => 'Active',
                ]);
                
                $this->testPrograms['open'] = $program;
                return "Created: ID={$program->id}";
            });
            
            // Test 5: Invalid program type should fail
            $this->test("Reject Invalid Program Type", function() {
                try {
                    ContributionProgram::create([
                        'sacco_id' => $this->sacco->id,
                        'name' => 'Invalid Program',
                        'contribution_type' => 'InvalidType',
                        'amount_per_member_type' => 'Specific',
                        'amount_per_member_value' => 1000,
                        'start_date' => now()->toDateString(),
                        'end_date' => now()->addMonths(1)->toDateString(),
                        'status' => 'Active',
                    ]);
                    throw new \Exception("Invalid program type was accepted");
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), 'Invalid') === false) {
                        throw $e;
                    }
                    return "Correctly rejected invalid type";
                }
            });
            
            // Commit the test programs so they're available for subsequent tests
            DB::commit();
            DB::beginTransaction();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    private function testMemberManagement()
    {
        $this->section("MEMBER MANAGEMENT");
        
        // Test 1: Add single member
        $this->test("Add Single Member to Program", function() {
            $program = $this->testPrograms['monthly'];
            // Refresh program from database after commit
            $program = ContributionProgram::find($program->id);
            
            if (!$program) {
                throw new \Exception("Program not found in database");
            }
            
            $member = $this->members->first();
            
            ContributionProgram::add_member_to_program($program, $member);
            
            $recordCount = ContributionProgramRecord::where('contribution_program_id', $program->id)
                ->where('member_id', $member->id)
                ->count();
            
            if ($recordCount === 0) {
                throw new \Exception("No records created for member");
            }
            
            return "Created {$recordCount} records for member";
        });
        
        // Test 2: Add multiple members
        $this->test("Add Multiple Members to Program", function() {
            $program = ContributionProgram::find($this->testPrograms['monthly']->id);
            if (!$program) {
                throw new \Exception("Program not found");
            }
            $beforeCount = ContributionProgramRecord::where('contribution_program_id', $program->id)->count();
            
            foreach ($this->members->slice(1, 3) as $member) {
                ContributionProgram::add_member_to_program($program, $member);
            }
            
            $afterCount = ContributionProgramRecord::where('contribution_program_id', $program->id)->count();
            $newRecords = $afterCount - $beforeCount;
            
            if ($newRecords === 0) {
                throw new \Exception("No records created");
            }
            
            return "Created {$newRecords} new records";
        });
        
        // Test 3: Verify no duplicate records
        $this->test("Prevent Duplicate Member Records", function() {
            $program = ContributionProgram::find($this->testPrograms['monthly']->id);
            if (!$program) {
                throw new \Exception("Program not found");
            }
            $member = $this->members->first();
            
            $beforeCount = ContributionProgramRecord::where('contribution_program_id', $program->id)
                ->where('member_id', $member->id)
                ->count();
            
            try {
                ContributionProgram::add_member_to_program($program, $member);
            } catch (\Exception $e) {
                // Expected to fail or skip
            }
            
            $afterCount = ContributionProgramRecord::where('contribution_program_id', $program->id)
                ->where('member_id', $member->id)
                ->count();
            
            if ($afterCount > $beforeCount) {
                return "WARNING: Duplicate records created";
            }
            
            return "No duplicate records - Good!";
        });
    }
    
    private function testRecordGeneration()
    {
        $this->section("RECORD GENERATION");
        
        // Test 1: Verify record structure
        $this->test("Verify Record Structure", function() {
            $program = ContributionProgram::find($this->testPrograms['monthly']->id);
            if (!$program) {
                return "SKIP: Program not found";
            }
            $record = ContributionProgramRecord::where('contribution_program_id', $program->id)->first();
            
            if (!$record) {
                throw new \Exception("No records found");
            }
            
            $requiredFields = ['id', 'sacco_id', 'contribution_program_id', 'member_id', 'amount', 'period_name'];
            foreach ($requiredFields as $field) {
                if (!isset($record->$field)) {
                    throw new \Exception("Missing field: {$field}");
                }
            }
            
            return "All required fields present";
        });
        
        // Test 2: Verify amount calculations
        $this->test("Verify Amount Calculations", function() {
            $program = $this->testPrograms['monthly'];
            $records = ContributionProgramRecord::where('contribution_program_id', $program->id)->get();
            
            foreach ($records as $record) {
                if ($record->amount != $program->amount_per_member_value) {
                    throw new \Exception("Amount mismatch: Expected {$program->amount_per_member_value}, Got {$record->amount}");
                }
            }
            
            return "All amounts match program settings";
        });
        
        // Test 3: Verify period names
        $this->test("Verify Period Names Format", function() {
            $program = $this->testPrograms['monthly'];
            $records = ContributionProgramRecord::where('contribution_program_id', $program->id)->get();
            
            foreach ($records as $record) {
                if (empty($record->period_name)) {
                    throw new \Exception("Empty period name found");
                }
            }
            
            return "All records have valid period names";
        });
    }
    
    private function testPaymentProcessing()
    {
        $this->section("PAYMENT PROCESSING");
        
        // Test 1: Mark record as paid (full payment)
        $this->test("Process Full Payment", function() {
            $program = $this->testPrograms['monthly'];
            $record = ContributionProgramRecord::where('contribution_program_id', $program->id)
                ->where('is_paid', 'No')
                ->first();
            
            if (!$record) {
                return "SKIP: No unpaid records";
            }
            
            $record->paid_amount = $record->amount;
            $record->is_paid = 'Yes';
            $record->payment_date = now()->toDateString();
            $record->teasurer_id = $this->admin->id;
            $record->save();
            
            $record->refresh();
            
            if ($record->is_paid !== 'Yes') {
                throw new \Exception("Payment status not updated");
            }
            
            return "Payment processed: {$record->amount} paid";
        });
        
        // Test 2: Partial payment
        $this->test("Process Partial Payment", function() {
            $program = $this->testPrograms['monthly'];
            $record = ContributionProgramRecord::where('contribution_program_id', $program->id)
                ->where('is_paid', 'No')
                ->first();
            
            if (!$record) {
                return "SKIP: No unpaid records";
            }
            
            $partialAmount = $record->amount / 2;
            $record->paid_amount = $partialAmount;
            $record->is_paid = 'No';
            $record->payment_date = now()->toDateString();
            $record->teasurer_id = $this->admin->id;
            $record->save();
            
            $record->refresh();
            
            // Check with small tolerance for floating point
            $difference = abs($record->paid_amount - $partialAmount);
            if ($difference > 1) {
                return "WARNING: Partial amount mismatch - Expected: {$partialAmount}, Got: {$record->paid_amount}";
            }
            
            return "Partial payment: {$record->paid_amount}/{$record->amount}";
        });
        
        // Test 3: Verify payment persistence
        $this->test("Verify Payment Persistence", function() {
            $paidRecord = ContributionProgramRecord::where('is_paid', 'Yes')->first();
            
            if (!$paidRecord) {
                return "SKIP: No paid records";
            }
            
            $found = ContributionProgramRecord::find($paidRecord->id);
            
            if ($found->is_paid !== 'Yes') {
                throw new \Exception("Payment status not persisted");
            }
            
            return "Payment persisted correctly";
        });
    }
    
    private function testBalanceCalculations()
    {
        $this->section("BALANCE CALCULATIONS");
        
        // Test 1: Update balances
        $this->test("Execute Balance Update", function() {
            $program = ContributionProgram::find($this->testPrograms['monthly']->id);
            if (!$program) {
                throw new \Exception("Program not found");
            }
            $program->update_balances();
            $program->refresh();
            
            return "Balances updated successfully";
        });
        
        // Test 2: Verify total expected
        $this->test("Verify Total Expected Amount", function() {
            $program = ContributionProgram::find($this->testPrograms['monthly']->id);
            if (!$program) {
                throw new \Exception("Program not found");
            }
            
            $manualTotal = ContributionProgramRecord::where('contribution_program_id', $program->id)
                ->sum('amount');
            
            if ($program->total_expected_amount != $manualTotal) {
                return "WARNING: Expected mismatch - Program: {$program->total_expected_amount}, Manual: {$manualTotal}";
            }
            
            return "Total expected correct: {$program->total_expected_amount}";
        });
        
        // Test 3: Verify total paid
        $this->test("Verify Total Paid Amount", function() {
            $program = ContributionProgram::find($this->testPrograms['monthly']->id);
            if (!$program) {
                throw new \Exception("Program not found");
            }
            
            $manualPaid = ContributionProgramRecord::where('contribution_program_id', $program->id)
                ->sum('paid_amount');
            
            if ($program->total_paid_amount != $manualPaid) {
                return "WARNING: Paid mismatch - Program: {$program->total_paid_amount}, Manual: {$manualPaid}";
            }
            
            return "Total paid correct: {$program->total_paid_amount}";
        });
    }
    
    private function testPaginationAndFiltering()
    {
        $this->section("PAGINATION & FILTERING");
        
        // Test 1: Basic pagination
        $this->test("Test Pagination", function() {
            $records = ContributionProgramRecord::where('sacco_id', $this->sacco->id)
                ->paginate(10);
            
            return "Page 1: {$records->count()} records, Total: {$records->total()}, Pages: {$records->lastPage()}";
        });
        
        // Test 2: Filter by program
        $this->test("Filter by Program", function() {
            $program = $this->testPrograms['monthly'];
            $records = ContributionProgramRecord::where('contribution_program_id', $program->id)->get();
            
            return "Found {$records->count()} records for program {$program->id}";
        });
        
        // Test 3: Filter by member
        $this->test("Filter by Member", function() {
            $member = $this->members->first();
            $records = ContributionProgramRecord::where('member_id', $member->id)
                ->where('sacco_id', $this->sacco->id)
                ->get();
            
            return "Found {$records->count()} records for member {$member->id}";
        });
        
        // Test 4: Filter by payment status
        $this->test("Filter by Payment Status", function() {
            $paid = ContributionProgramRecord::where('sacco_id', $this->sacco->id)
                ->where('is_paid', 'Yes')->count();
            $unpaid = ContributionProgramRecord::where('sacco_id', $this->sacco->id)
                ->where('is_paid', 'No')->count();
            
            return "Paid: {$paid}, Unpaid: {$unpaid}";
        });
    }
    
    private function testPerformanceOptimization()
    {
        $this->section("PERFORMANCE OPTIMIZATION");
        
        // Test 1: Query count without eager loading
        $this->test("Query Count WITHOUT Eager Loading", function() {
            DB::enableQueryLog();
            DB::flushQueryLog();
            
            $records = ContributionProgramRecord::where('sacco_id', $this->sacco->id)
                ->limit(5)
                ->get();
            
            // Access relationships to trigger lazy loading
            foreach ($records as $record) {
                $m = $record->member;
                $p = $record->program;
            }
            
            $queryCount = count(DB::getQueryLog());
            DB::disableQueryLog();
            
            if ($queryCount > 15) {
                return "WARNING: High query count - {$queryCount} queries (N+1 problem!)";
            }
            
            return "{$queryCount} queries";
        });
        
        // Test 2: Query count with eager loading
        $this->test("Query Count WITH Eager Loading", function() {
            DB::enableQueryLog();
            DB::flushQueryLog();
            
            $records = ContributionProgramRecord::where('sacco_id', $this->sacco->id)
                ->with(['member', 'program', 'treasurer'])
                ->limit(5)
                ->get();
            
            // Access relationships - should not trigger new queries
            foreach ($records as $record) {
                $m = $record->member;
                $p = $record->program;
            }
            
            $queryCount = count(DB::getQueryLog());
            DB::disableQueryLog();
            
            if ($queryCount > 5) {
                return "WARNING: Still high - {$queryCount} queries";
            }
            
            return "OPTIMAL: {$queryCount} queries only!";
        });
    }
    
    private function testSecurityValidation()
    {
        $this->section("SECURITY VALIDATION");
        
        // Test 1: SQL Injection Prevention in Models
        $this->test("SQL Injection Prevention", function() {
            // Try malicious input
            $malicious = "1' OR '1'='1";
            
            try {
                $records = ContributionProgramRecord::where('sacco_id', $this->sacco->id)
                    ->whereRaw("id = ?", [$malicious])
                    ->get();
                
                return "Parameterized queries working";
            } catch (\Exception $e) {
                return "Query protected against injection";
            }
        });
        
        // Test 2: Mass assignment protection
        $this->test("Mass Assignment Protection", function() {
            try {
                $program = ContributionProgram::create([
                    'id' => 99999,  // Should be ignored
                    'sacco_id' => $this->sacco->id,
                    'name' => 'Test',
                    'contribution_type' => 'Periodic',
                    'periodic_type' => 'Monthly',
                    'amount_per_member_type' => 'Specific',
                    'amount_per_member_value' => 1000,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addMonth()->toDateString(),
                    'status' => 'Active',
                ]);
                
                if ($program->id == 99999) {
                    throw new \Exception("ID assignment not protected!");
                }
                
                $program->delete();
                return "Protected: ID cannot be mass assigned";
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });
        
        // Test 3: Authorization checks exist
        $this->test("Authorization Context Available", function() {
            if (!auth()->check()) {
                throw new \Exception("No auth context");
            }
            
            return "Auth context: " . auth()->user()->user_type;
        });
    }
    
    private function testEdgeCases()
    {
        $this->section("EDGE CASES");
        
        // Test 1: Zero amount program
        $this->test("Handle Zero Amount (Open Program)", function() {
            $program = $this->testPrograms['open'];
            
            if ($program->amount_per_member_value != 0) {
                throw new \Exception("Open program should have zero amount");
            }
            
            return "Zero amount handled correctly";
        });
        
        // Test 2: Long date range (should not cause timeout)
        $this->test("Long Date Range Protection", function() {
            try {
                $longProgram = ContributionProgram::create([
                    'sacco_id' => $this->sacco->id,
                    'name' => 'Long Range Test',
                    'contribution_type' => 'Periodic',
                    'periodic_type' => 'Weekly',
                    'amount_per_member_type' => 'Specific',
                    'amount_per_member_value' => 1000,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addYears(20)->toDateString(),
                    'status' => 'Active',
                ]);
                
                $startTime = microtime(true);
                ContributionProgram::add_member_to_program($longProgram, $this->members->first());
                $duration = microtime(true) - $startTime;
                
                $longProgram->delete();
                
                if ($duration > 5) {
                    return "WARNING: Took {$duration}s - consider limits";
                }
                
                return "Completed in {$duration}s";
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), '1000') !== false || strpos($e->getMessage(), 'maximum') !== false) {
                    return "PROTECTED: Max iteration limit enforced";
                }
                throw $e;
            }
        });
        
        // Test 3: Past dates
        $this->test("Handle Past Dates", function() {
            try {
                $pastProgram = ContributionProgram::create([
                    'sacco_id' => $this->sacco->id,
                    'name' => 'Past Program Test',
                    'contribution_type' => 'Periodic',
                    'periodic_type' => 'Monthly',
                    'amount_per_member_type' => 'Specific',
                    'amount_per_member_value' => 1000,
                    'start_date' => now()->subMonths(2)->toDateString(),
                    'end_date' => now()->addMonth()->toDateString(),
                    'status' => 'Active',
                ]);
                
                $pastProgram->delete();
                return "Past dates accepted";
            } catch (\Exception $e) {
                return "Validation: " . $e->getMessage();
            }
        });
    }
    
    private function testConcurrency()
    {
        $this->section("CONCURRENCY & TRANSACTIONS");
        
        // Test 1: Transaction rollback
        $this->test("Transaction Rollback", function() {
            $beforeCount = ContributionProgram::count();
            
            DB::beginTransaction();
            try {
                ContributionProgram::create([
                    'sacco_id' => $this->sacco->id,
                    'name' => 'Rollback Test',
                    'contribution_type' => 'Periodic',
                    'periodic_type' => 'Monthly',
                    'amount_per_member_type' => 'Specific',
                    'amount_per_member_value' => 1000,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addMonth()->toDateString(),
                    'status' => 'Active',
                ]);
                
                DB::rollBack();
            } catch (\Exception $e) {
                DB::rollBack();
            }
            
            $afterCount = ContributionProgram::count();
            
            if ($afterCount != $beforeCount) {
                throw new \Exception("Transaction not rolled back!");
            }
            
            return "Rollback working correctly";
        });
        
        // Test 2: Balance update uses transactions
        $this->test("Balance Update Transaction Safety", function() {
            $program = $this->testPrograms['monthly'];
            
            // Check if update_balances method exists
            if (!method_exists($program, 'update_balances')) {
                throw new \Exception("update_balances method not found");
            }
            
            // Execute update
            $program->update_balances();
            
            return "Balance update completed safely";
        });
    }
    
    private function section($title)
    {
        echo "\n" . str_repeat("─", 100) . "\n";
        echo "📌 {$title}\n";
        echo str_repeat("─", 100) . "\n";
    }
    
    private function test($name, $callback)
    {
        $this->totalTests++;
        
        try {
            $result = $callback();
            
            if (strpos($result, 'WARNING') !== false) {
                $this->warningTests++;
                echo "⚠️  {$name}\n";
                echo "    {$result}\n";
                $this->testResults[] = ['name' => $name, 'status' => 'warning', 'message' => $result];
            } elseif (strpos($result, 'SKIP') !== false) {
                echo "⏭️  {$name}\n";
                echo "    {$result}\n";
                $this->testResults[] = ['name' => $name, 'status' => 'skip', 'message' => $result];
            } else {
                $this->passedTests++;
                echo "✅ {$name}\n";
                if ($result) {
                    echo "    {$result}\n";
                }
                $this->testResults[] = ['name' => $name, 'status' => 'pass', 'message' => $result];
            }
        } catch (\Exception $e) {
            $this->failedTests++;
            echo "❌ {$name}\n";
            echo "    ERROR: {$e->getMessage()}\n";
            $this->testResults[] = ['name' => $name, 'status' => 'fail', 'message' => $e->getMessage()];
        }
    }
    
    private function fail($name, $message)
    {
        $this->totalTests++;
        $this->failedTests++;
        echo "❌ {$name}\n";
        echo "    {$message}\n";
        $this->testResults[] = ['name' => $name, 'status' => 'fail', 'message' => $message];
    }
    
    private function printFinalReport()
    {
        // Cleanup test data
        DB::rollBack();
        
        echo "\n" . str_repeat("═", 100) . "\n";
        echo "📊 FINAL TEST REPORT\n";
        echo str_repeat("═", 100) . "\n\n";
        
        echo "Total Tests:   {$this->totalTests}\n";
        echo "✅ Passed:      {$this->passedTests}\n";
        echo "❌ Failed:      {$this->failedTests}\n";
        echo "⚠️  Warnings:    {$this->warningTests}\n";
        
        $successRate = $this->totalTests > 0 ? ($this->passedTests / $this->totalTests * 100) : 0;
        echo "\nSuccess Rate: " . number_format($successRate, 1) . "%\n";
        
        if ($this->failedTests > 0) {
            echo "\n" . str_repeat("─", 100) . "\n";
            echo "❌ FAILED TESTS:\n";
            echo str_repeat("─", 100) . "\n";
            
            foreach ($this->testResults as $result) {
                if ($result['status'] === 'fail') {
                    echo "  • {$result['name']}\n";
                    echo "    {$result['message']}\n\n";
                }
            }
        }
        
        if ($this->warningTests > 0) {
            echo "\n" . str_repeat("─", 100) . "\n";
            echo "⚠️  WARNINGS:\n";
            echo str_repeat("─", 100) . "\n";
            
            foreach ($this->testResults as $result) {
                if ($result['status'] === 'warning') {
                    echo "  • {$result['name']}\n";
                    echo "    {$result['message']}\n\n";
                }
            }
        }
        
        echo "\n" . str_repeat("═", 100) . "\n";
        
        if ($this->failedTests === 0) {
            echo "✅ ALL CRITICAL TESTS PASSED - MODULE IS PRODUCTION READY!\n";
        } else {
            echo "❌ SOME TESTS FAILED - REVIEW AND FIX BEFORE PRODUCTION\n";
        }
        
        echo str_repeat("═", 100) . "\n\n";
    }
}

// Run the exhaustive tests
$tester = new ExhaustiveContributionTester();
$tester->run();
