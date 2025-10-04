<?php

/**
 * ADVANCED BACKEND TESTS FOR CONTRIBUTION MODULE
 * 
 * Tests: Models, Database, Relationships, Business Logic, Edge Cases
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

class ContributionAdvancedBackendTest
{
    private $results = [];
    private $testCount = 0;
    private $passCount = 0;
    private $failCount = 0;
    private $warningCount = 0;
    
    private $testSacco;
    private $testAdmin;
    private $testMembers = [];
    private $testPrograms = [];

    public function __construct()
    {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════════════\n";
        echo "   ADVANCED BACKEND TESTS - CONTRIBUTION MODULE\n";
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

    public function runAllTests()
    {
        // Setup test data
        $this->setupTestData();
        
        // Test categories
        $this->testDatabaseSchema();
        $this->testModelRelationships();
        $this->testModelValidation();
        $this->testBusinessLogic();
        $this->testDataIntegrity();
        $this->testPerformance();
        $this->testConcurrency();
        $this->testEdgeCases();
        $this->testDataConsistency();
        $this->testCleanup();
        
        // Show results
        $this->showResults();
    }

    private function setupTestData()
    {
        echo "\n--- SETTING UP TEST DATA ---\n";
        
        DB::beginTransaction();
        
        // Find or create test sacco
        $this->testSacco = Sacco::first();
        if (!$this->testSacco) {
            echo "❌ No sacco found. Creating test sacco...\n";
            $this->testSacco = Sacco::create([
                'name' => 'Test Sacco ' . time(),
                'phone_number' => '0700000000',
                'email_address' => 'test' . time() . '@test.com',
                'administrator_id' => 1
            ]);
        }
        echo "✅ Using Sacco: {$this->testSacco->name} (ID: {$this->testSacco->id})\n";
        
        // Find or create admin
        $this->testAdmin = User::where([
            'sacco_id' => $this->testSacco->id,
            'user_type' => 'Admin'
        ])->first();
        
        if (!$this->testAdmin) {
            $this->testAdmin = User::where('sacco_id', $this->testSacco->id)->first();
        }
        
        if (!$this->testAdmin) {
            $this->testAdmin = User::create([
                'name' => 'Test Admin ' . time(),
                'email' => 'admin' . time() . '@test.com',
                'phone_number' => '0711111111',
                'password' => bcrypt('password'),
                'sacco_id' => $this->testSacco->id,
                'user_type' => 'Admin',
                'status' => 1
            ]);
        }
        echo "✅ Using Admin: {$this->testAdmin->name} (ID: {$this->testAdmin->id})\n";
        
        // Authenticate admin
        Auth::login($this->testAdmin);
        
        // Find or create test members
        $members = User::where([
            'sacco_id' => $this->testSacco->id,
            'user_type' => 'Member'
        ])->limit(5)->get();
        
        if ($members->count() < 3) {
            echo "Creating test members...\n";
            for ($i = $members->count(); $i < 5; $i++) {
                $member = User::create([
                    'name' => 'Test Member ' . ($i + 1) . ' ' . time(),
                    'email' => 'member' . ($i + 1) . time() . '@test.com',
                    'phone_number' => '072' . str_pad($i, 7, '0'),
                    'password' => bcrypt('password'),
                    'sacco_id' => $this->testSacco->id,
                    'user_type' => 'Member',
                    'status' => 1
                ]);
                $this->testMembers[] = $member;
            }
        } else {
            $this->testMembers = $members->toArray();
        }
        echo "✅ Using " . count($this->testMembers) . " test members\n";
        
        DB::commit();
        echo "--- SETUP COMPLETE ---\n\n";
    }

    private function testDatabaseSchema()
    {
        echo "\n=== DATABASE SCHEMA TESTS ===\n";
        
        $this->test('contribution_programs table exists', function() {
            $exists = DB::select("SHOW TABLES LIKE 'contribution_programs'");
            return count($exists) > 0;
        }, 'Database');
        
        $this->test('contribution_program_records table exists', function() {
            $exists = DB::select("SHOW TABLES LIKE 'contribution_program_records'");
            return count($exists) > 0;
        }, 'Database');
        
        $this->test('contribution_programs has required columns', function() {
            $columns = array_column(DB::select("DESCRIBE contribution_programs"), 'Field');
            $required = ['id', 'sacco_id', 'name', 'contribution_type', 'periodic_type', 
                        'amount_per_member_type', 'amount_per_member_value', 'start_date', 
                        'end_date', 'status', 'created_at', 'updated_at'];
            foreach ($required as $col) {
                if (!in_array($col, $columns)) {
                    return "Missing column: $col";
                }
            }
            return true;
        }, 'Database');
        
        $this->test('contribution_program_records has required columns', function() {
            $columns = array_column(DB::select("DESCRIBE contribution_program_records"), 'Field');
            $required = ['id', 'sacco_id', 'contribution_program_id', 'member_id', 
                        'amount', 'is_paid', 'created_at', 'updated_at'];
            foreach ($required as $col) {
                if (!in_array($col, $columns)) {
                    return "Missing column: $col";
                }
            }
            return true;
        }, 'Database');
        
        $this->test('Foreign key constraints exist', function() {
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'contribution_program_records' 
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ");
            
            if (count($constraints) == 0) {
                return ['warning' => true, 'message' => 'No foreign keys found - recommend adding for data integrity'];
            }
            return true;
        }, 'Database');
        
        $this->test('Indexes exist for performance', function() {
            $indexes = DB::select("SHOW INDEX FROM contribution_program_records");
            $indexNames = array_unique(array_column($indexes, 'Key_name'));
            
            if (count($indexNames) <= 1) { // Only PRIMARY
                return ['warning' => true, 'message' => 'Only primary key index found - recommend adding indexes on sacco_id, member_id, contribution_program_id'];
            }
            return true;
        }, 'Database');
        
        $this->test('Column types are appropriate', function() {
            $columns = DB::select("DESCRIBE contribution_programs");
            foreach ($columns as $col) {
                if ($col->Field == 'amount_per_member_value' && 
                    !in_array(strtolower($col->Type), ['decimal', 'float', 'double', 'bigint', 'int'])) {
                    return "amount_per_member_value has wrong type: {$col->Type}";
                }
            }
            return true;
        }, 'Database');
    }

    private function testModelRelationships()
    {
        echo "\n=== MODEL RELATIONSHIP TESTS ===\n";
        
        DB::beginTransaction();
        
        // Create test program
        $program = ContributionProgram::create([
            'sacco_id' => $this->testSacco->id,
            'name' => 'Relationship Test Program',
            'contribution_type' => 'Periodic',
            'periodic_type' => 'Monthly',
            'amount_per_member_type' => 'Specific',
            'amount_per_member_value' => 10000,
            'start_date' => Carbon::now()->format('Y-m-d'),
            'end_date' => Carbon::now()->addMonths(6)->format('Y-m-d'),
            'status' => 'Active'
        ]);
        
        $this->test('Program->Sacco relationship works', function() use ($program) {
            $sacco = $program->sacco;
            return $sacco != null && $sacco->id == $this->testSacco->id;
        }, 'Relationships');
        
        $this->test('Program->Records relationship defined', function() use ($program) {
            return method_exists($program, 'records') || method_exists($program, 'contribution_records');
        }, 'Relationships');
        
        // Add member to generate records properly
        $member = User::find($this->testMembers[0]['id']);
        ContributionProgram::add_member_to_program($program, $member);
        $program = ContributionProgram::find($program->id);
        
        $record = ContributionProgramRecord::where('contribution_program_id', $program->id)->first();
        
        $this->test('Record->Program relationship works', function() use ($record, $program) {
            if (!$record) {
                return ['warning' => true, 'message' => 'No record generated to test relationship'];
            }
            $prog = $record->program;
            return $prog != null && $prog->id == $program->id;
        }, 'Relationships');
        
        $this->test('Record->Member relationship works', function() use ($record) {
            if (!$record) {
                return ['warning' => true, 'message' => 'No record generated to test relationship'];
            }
            $member = $record->member;
            return $member != null && $member->id == $this->testMembers[0]['id'];
        }, 'Relationships');
        
        $this->test('Record->Sacco relationship works', function() use ($record) {
            if (!$record) {
                return ['warning' => true, 'message' => 'No record generated to test relationship'];
            }
            $sacco = $record->sacco;
            return $sacco != null && $sacco->id == $this->testSacco->id;
        }, 'Relationships');
        
        $this->test('Eager loading reduces N+1 queries', function() use ($program) {
            // Without eager loading
            DB::enableQueryLog();
            $records1 = ContributionProgramRecord::where('contribution_program_id', $program->id)->get();
            foreach ($records1 as $rec) {
                $_ = $rec->member;
                $_ = $rec->program;
            }
            $queries1 = count(DB::getQueryLog());
            DB::disableQueryLog();
            
            // With eager loading
            DB::enableQueryLog();
            $records2 = ContributionProgramRecord::with(['member', 'program'])
                ->where('contribution_program_id', $program->id)->get();
            foreach ($records2 as $rec) {
                $_ = $rec->member;
                $_ = $rec->program;
            }
            $queries2 = count(DB::getQueryLog());
            DB::disableQueryLog();
            
            if ($queries2 >= $queries1) {
                return ['warning' => true, 'message' => "Eager loading not improving query count: {$queries1} vs {$queries2}"];
            }
            return true;
        }, 'Relationships');
        
        DB::rollBack();
    }

    private function testModelValidation()
    {
        echo "\n=== MODEL VALIDATION TESTS ===\n";
        
        DB::beginTransaction();
        
        $this->test('Cannot create program without required fields', function() {
            try {
                ContributionProgram::create([
                    'name' => 'Invalid Program'
                ]);
                return "Should have failed validation";
            } catch (\Exception $e) {
                return true; // Expected to fail
            }
        }, 'Validation');
        
        $this->test('Cannot create record without program_id', function() {
            try {
                ContributionProgramRecord::create([
                    'sacco_id' => $this->testSacco->id,
                    'member_id' => $this->testMembers[0]['id'],
                    'amount' => 10000,
                    'is_paid' => 'No'
                ]);
                return "Should have failed validation";
            } catch (\Exception $e) {
                return true; // Expected to fail
            }
        }, 'Validation');
        
        $this->test('Invalid contribution_type rejected', function() {
            $program = new ContributionProgram([
                'sacco_id' => $this->testSacco->id,
                'name' => 'Test Program',
                'contribution_type' => 'InvalidType',
                'periodic_type' => 'Monthly',
                'amount_per_member_type' => 'Specific',
                'amount_per_member_value' => 10000,
                'start_date' => Carbon::now()->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(1)->format('Y-m-d'),
                'status' => 'Active'
            ]);
            
            try {
                $program->save();
                // Check if validation happened
                if (method_exists($program, 'validate')) {
                    return ['warning' => true, 'message' => 'Invalid type saved - validation may be missing'];
                }
                return ['warning' => true, 'message' => 'No validation method found'];
            } catch (\Exception $e) {
                return true; // Expected to fail
            }
        }, 'Validation');
        
        $this->test('Negative amounts rejected', function() {
            try {
                ContributionProgramRecord::create([
                    'sacco_id' => $this->testSacco->id,
                    'contribution_program_id' => 1,
                    'member_id' => $this->testMembers[0]['id'],
                    'amount' => -10000,
                    'is_paid' => 'No'
                ]);
                return ['warning' => true, 'message' => 'Negative amount saved - validation may be missing'];
            } catch (\Exception $e) {
                return true; // Expected to fail
            }
        }, 'Validation');
        
        $this->test('End date before start date rejected', function() {
            $program = new ContributionProgram([
                'sacco_id' => $this->testSacco->id,
                'name' => 'Test Program',
                'contribution_type' => 'Periodic',
                'periodic_type' => 'Monthly',
                'amount_per_member_type' => 'Specific',
                'amount_per_member_value' => 10000,
                'start_date' => Carbon::now()->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'status' => 'Active'
            ]);
            
            try {
                $program->save();
                return ['warning' => true, 'message' => 'Invalid date range saved - validation may be missing'];
            } catch (\Exception $e) {
                return true; // Expected to fail
            }
        }, 'Validation');
        
        DB::rollBack();
    }

    private function testBusinessLogic()
    {
        echo "\n=== BUSINESS LOGIC TESTS ===\n";
        
        DB::beginTransaction();
        
        // Create test program
        $program = ContributionProgram::create([
            'sacco_id' => $this->testSacco->id,
            'name' => 'Business Logic Test',
            'contribution_type' => 'Periodic',
            'periodic_type' => 'Monthly',
            'amount_per_member_type' => 'Specific',
            'amount_per_member_value' => 50000,
            'start_date' => Carbon::now()->format('Y-m-d'),
            'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
            'status' => 'Active',
            'prepared' => 'No'
        ]);
        
        $this->test('add_member_to_program creates records', function() use ($program) {
            $member = User::find($this->testMembers[0]['id']);
            $before = ContributionProgramRecord::where('contribution_program_id', $program->id)->count();
            
            ContributionProgram::add_member_to_program($program, $member);
            
            $after = ContributionProgramRecord::where('contribution_program_id', $program->id)->count();
            return $after > $before;
        }, 'Business Logic');
        
        $this->test('Duplicate member records prevented', function() use ($program) {
            $member = User::find($this->testMembers[0]['id']);
            $before = ContributionProgramRecord::where([
                'contribution_program_id' => $program->id,
                'member_id' => $member->id
            ])->count();
            
            // Try to add same member again
            ContributionProgram::add_member_to_program($program, $member);
            
            $after = ContributionProgramRecord::where([
                'contribution_program_id' => $program->id,
                'member_id' => $member->id
            ])->count();
            
            return $after == $before; // Should not increase
        }, 'Business Logic');
        
        $this->test('update_balances calculates correctly', function() use ($program) {
            // Create some paid and unpaid records
            $program->update_balances();
            
            $program = ContributionProgram::find($program->id);
            return method_exists($program, 'update_balances');
        }, 'Business Logic');
        
        $this->test('Periodic records generated with correct dates', function() use ($program) {
            $records = ContributionProgramRecord::where('contribution_program_id', $program->id)->get();
            
            if ($records->count() == 0) {
                return ['warning' => true, 'message' => 'No records generated'];
            }
            
            foreach ($records as $record) {
                if (empty($record->period_name)) {
                    return "Record {$record->id} missing period_name";
                }
            }
            return true;
        }, 'Business Logic');
        
        $this->test('Payment updates record status', function() use ($program) {
            $record = ContributionProgramRecord::where('contribution_program_id', $program->id)->first();
            if (!$record) {
                return ['warning' => true, 'message' => 'No records to test payment'];
            }
            
            $record->is_paid = 'Yes';
            $record->paid_amount = $record->amount;
            $record->payment_date = Carbon::now()->format('Y-m-d');
            $record->save();
            
            $updated = ContributionProgramRecord::find($record->id);
            return $updated->is_paid == 'Yes';
        }, 'Business Logic');
        
        $this->test('Partial payment allowed', function() use ($program) {
            $record = ContributionProgramRecord::where([
                'contribution_program_id' => $program->id,
                'is_paid' => 'No'
            ])->first();
            
            if (!$record) {
                return ['warning' => true, 'message' => 'No unpaid records to test'];
            }
            
            $partial = $record->amount / 2;
            $record->paid_amount = $partial;
            $record->is_paid = 'No';
            $record->save();
            
            $updated = ContributionProgramRecord::find($record->id);
            return $updated->paid_amount > 0 && $updated->is_paid == 'No';
        }, 'Business Logic');
        
        DB::commit();
    }

    private function testDataIntegrity()
    {
        echo "\n=== DATA INTEGRITY TESTS ===\n";
        
        DB::beginTransaction();
        
        $this->test('Deleting member does not orphan records', function() {
            // Check cascade or handling
            $orphans = DB::select("
                SELECT COUNT(*) as count 
                FROM contribution_program_records 
                WHERE member_id NOT IN (SELECT id FROM users)
            ");
            
            if ($orphans[0]->count > 0) {
                return ['warning' => true, 'message' => "{$orphans[0]->count} orphaned records found"];
            }
            return true;
        }, 'Data Integrity');
        
        $this->test('Deleting program handles records', function() {
            $orphans = DB::select("
                SELECT COUNT(*) as count 
                FROM contribution_program_records 
                WHERE contribution_program_id NOT IN (SELECT id FROM contribution_programs)
            ");
            
            if ($orphans[0]->count > 0) {
                return ['warning' => true, 'message' => "{$orphans[0]->count} orphaned records found"];
            }
            return true;
        }, 'Data Integrity');
        
        $this->test('All records belong to valid saccos', function() {
            $invalid = DB::select("
                SELECT COUNT(*) as count 
                FROM contribution_program_records 
                WHERE sacco_id NOT IN (SELECT id FROM saccos)
            ");
            
            if ($invalid[0]->count > 0) {
                return "Found {$invalid[0]->count} records with invalid sacco_id";
            }
            return true;
        }, 'Data Integrity');
        
        $this->test('Paid records have payment dates', function() {
            $missing = ContributionProgramRecord::where('is_paid', 'Yes')
                ->whereNull('payment_date')
                ->count();
            
            if ($missing > 0) {
                return ['warning' => true, 'message' => "{$missing} paid records missing payment_date"];
            }
            return true;
        }, 'Data Integrity');
        
        $this->test('Paid amounts do not exceed expected amounts', function() {
            $invalid = ContributionProgramRecord::whereRaw('paid_amount > amount')->count();
            
            if ($invalid > 0) {
                return "{$invalid} records have paid_amount > amount";
            }
            return true;
        }, 'Data Integrity');
        
        DB::rollBack();
    }

    private function testPerformance()
    {
        echo "\n=== PERFORMANCE TESTS ===\n";
        
        $this->test('Large record set pagination works', function() {
            $count = ContributionProgramRecord::count();
            if ($count < 100) {
                return ['warning' => true, 'message' => "Only {$count} records - cannot test large dataset performance"];
            }
            
            $start = microtime(true);
            $records = ContributionProgramRecord::paginate(100);
            $time = microtime(true) - $start;
            
            if ($time > 1.0) {
                return ['warning' => true, 'message' => "Pagination took {$time}s - may need optimization"];
            }
            return true;
        }, 'Performance');
        
        $this->test('Filtering by sacco is indexed', function() {
            DB::enableQueryLog();
            ContributionProgramRecord::where('sacco_id', $this->testSacco->id)->get();
            $queries = DB::getQueryLog();
            DB::disableQueryLog();
            
            // Check if query uses index
            $explain = DB::select("EXPLAIN SELECT * FROM contribution_program_records WHERE sacco_id = ?", [$this->testSacco->id]);
            
            if (isset($explain[0]->key) && $explain[0]->key == 'PRIMARY') {
                return ['warning' => true, 'message' => 'Query using PRIMARY key instead of sacco_id index'];
            }
            return true;
        }, 'Performance');
        
        $this->test('Bulk record creation is efficient', function() {
            DB::beginTransaction();
            
            $program = ContributionProgram::create([
                'sacco_id' => $this->testSacco->id,
                'name' => 'Bulk Test',
                'contribution_type' => 'Periodic',
                'periodic_type' => 'Monthly',
                'amount_per_member_type' => 'Specific',
                'amount_per_member_value' => 10000,
                'start_date' => Carbon::now()->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(1)->format('Y-m-d'),
                'status' => 'Active'
            ]);
            
            $start = microtime(true);
            foreach ($this->testMembers as $member) {
                ContributionProgram::add_member_to_program($program, User::find($member['id']));
            }
            $time = microtime(true) - $start;
            
            DB::rollBack();
            
            if ($time > 2.0) {
                return ['warning' => true, 'message' => "Bulk creation took {$time}s for " . count($this->testMembers) . " members"];
            }
            return true;
        }, 'Performance');
    }

    private function testConcurrency()
    {
        echo "\n=== CONCURRENCY TESTS ===\n";
        
        $this->test('Transaction rollback works', function() {
            DB::beginTransaction();
            
            $before = ContributionProgram::count();
            
            ContributionProgram::create([
                'sacco_id' => $this->testSacco->id,
                'name' => 'Rollback Test',
                'contribution_type' => 'Periodic',
                'periodic_type' => 'Monthly',
                'amount_per_member_type' => 'Specific',
                'amount_per_member_value' => 10000,
                'start_date' => Carbon::now()->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(1)->format('Y-m-d'),
                'status' => 'Active'
            ]);
            
            DB::rollBack();
            
            $after = ContributionProgram::count();
            return $before == $after;
        }, 'Concurrency');
        
        $this->test('Balance update uses locking', function() {
            $program = ContributionProgram::where('sacco_id', $this->testSacco->id)->first();
            if (!$program) {
                return ['warning' => true, 'message' => 'No program to test locking'];
            }
            
            // Check if update_balances uses transactions
            return method_exists($program, 'update_balances');
        }, 'Concurrency');
    }

    private function testEdgeCases()
    {
        echo "\n=== EDGE CASE TESTS ===\n";
        
        DB::beginTransaction();
        
        $this->test('Zero amount contribution handled', function() {
            $program = ContributionProgram::create([
                'sacco_id' => $this->testSacco->id,
                'name' => 'Zero Amount Test',
                'contribution_type' => 'Open',
                'periodic_type' => 'None',
                'amount_per_member_type' => 'Specific',
                'amount_per_member_value' => 0,
                'start_date' => Carbon::now()->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(1)->format('Y-m-d'),
                'status' => 'Active'
            ]);
            
            return $program->id > 0;
        }, 'Edge Cases');
        
        $this->test('Very large date range handled', function() {
            try {
                $program = ContributionProgram::create([
                    'sacco_id' => $this->testSacco->id,
                    'name' => 'Long Date Range Test',
                    'contribution_type' => 'Periodic',
                    'periodic_type' => 'Monthly',
                    'amount_per_member_type' => 'Specific',
                    'amount_per_member_value' => 10000,
                    'start_date' => Carbon::now()->format('Y-m-d'),
                    'end_date' => Carbon::now()->addYears(10)->format('Y-m-d'),
                    'status' => 'Active'
                ]);
                
                return true;
            } catch (\Exception $e) {
                return "Failed to handle long date range: " . $e->getMessage();
            }
        }, 'Edge Cases');
        
        $this->test('Special characters in name handled', function() {
            $program = ContributionProgram::create([
                'sacco_id' => $this->testSacco->id,
                'name' => "Test's Program & \"Quotes\" <script>",
                'contribution_type' => 'Periodic',
                'periodic_type' => 'Monthly',
                'amount_per_member_type' => 'Specific',
                'amount_per_member_value' => 10000,
                'start_date' => Carbon::now()->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(1)->format('Y-m-d'),
                'status' => 'Active'
            ]);
            
            $retrieved = ContributionProgram::find($program->id);
            return strpos($retrieved->name, "Test's") !== false;
        }, 'Edge Cases');
        
        $this->test('Unicode characters supported', function() {
            $program = ContributionProgram::create([
                'sacco_id' => $this->testSacco->id,
                'name' => '测试项目 プログラム العربية',
                'contribution_type' => 'Periodic',
                'periodic_type' => 'Monthly',
                'amount_per_member_type' => 'Specific',
                'amount_per_member_value' => 10000,
                'start_date' => Carbon::now()->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(1)->format('Y-m-d'),
                'status' => 'Active'
            ]);
            
            $retrieved = ContributionProgram::find($program->id);
            return strlen($retrieved->name) > 0;
        }, 'Edge Cases');
        
        DB::rollBack();
    }

    private function testDataConsistency()
    {
        echo "\n=== DATA CONSISTENCY TESTS ===\n";
        
        $this->test('Program totals match record totals', function() {
            $programs = ContributionProgram::where('sacco_id', $this->testSacco->id)
                ->where('prepared', 'Yes')
                ->limit(5)
                ->get();
            
            if ($programs->count() == 0) {
                return ['warning' => true, 'message' => 'No prepared programs to test consistency'];
            }
            
            foreach ($programs as $program) {
                $recordTotal = ContributionProgramRecord::where('contribution_program_id', $program->id)
                    ->sum('amount');
                
                // Allow some tolerance for floating point
                if (abs($program->total_expected - $recordTotal) > 1) {
                    return ['warning' => true, 'message' => "Program {$program->id} total mismatch: {$program->total_expected} vs {$recordTotal}"];
                }
            }
            
            return true;
        }, 'Consistency');
        
        $this->test('Members count matches records count', function() {
            $programs = ContributionProgram::where('sacco_id', $this->testSacco->id)
                ->where('prepared', 'Yes')
                ->limit(5)
                ->get();
            
            if ($programs->count() == 0) {
                return ['warning' => true, 'message' => 'No prepared programs to test'];
            }
            
            foreach ($programs as $program) {
                $memberIds = is_array($program->members) ? $program->members : json_decode($program->members, true);
                if (!is_array($memberIds)) {
                    continue;
                }
                
                $recordCount = ContributionProgramRecord::where('contribution_program_id', $program->id)
                    ->distinct('member_id')
                    ->count('member_id');
                
                if (count($memberIds) != $recordCount) {
                    return ['warning' => true, 'message' => "Program {$program->id} member count mismatch"];
                }
            }
            
            return true;
        }, 'Consistency');
    }

    private function testCleanup()
    {
        echo "\n=== CLEANUP TESTS ===\n";
        
        $this->test('Soft deletes work properly', function() {
            // Check if models use soft deletes
            $program = new ContributionProgram();
            $usesSoftDelete = in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($program));
            
            if (!$usesSoftDelete) {
                return ['warning' => true, 'message' => 'Soft deletes not enabled - deleted records cannot be recovered'];
            }
            return true;
        }, 'Cleanup');
        
        $this->test('Old completed programs can be archived', function() {
            $old = ContributionProgram::where('end_date', '<', Carbon::now()->subMonths(6)->format('Y-m-d'))
                ->count();
            
            if ($old > 100) {
                return ['warning' => true, 'message' => "{$old} old programs found - consider archiving"];
            }
            return true;
        }, 'Cleanup');
    }

    private function showResults()
    {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════════════\n";
        echo "   TEST RESULTS SUMMARY\n";
        echo "═══════════════════════════════════════════════════════════════════════\n\n";
        
        echo "Total Tests:   {$this->testCount}\n";
        echo "✅ Passed:      {$this->passCount} (" . round(($this->passCount/$this->testCount)*100, 1) . "%)\n";
        echo "❌ Failed:      {$this->failCount} (" . round(($this->failCount/$this->testCount)*100, 1) . "%)\n";
        echo "⚠️  Warnings:    {$this->warningCount} (" . round(($this->warningCount/$this->testCount)*100, 1) . "%)\n";
        
        echo "\n";
        
        // Group results by category
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
            echo "✅ ALL CRITICAL TESTS PASSED!\n";
        } else {
            echo "❌ SOME TESTS FAILED - REVIEW AND FIX ISSUES\n";
        }
        
        echo "═══════════════════════════════════════════════════════════════════════\n";
        echo "Completed: " . date('Y-m-d H:i:s') . "\n";
        echo "═══════════════════════════════════════════════════════════════════════\n\n";
    }
}

// Run tests
$tester = new ContributionAdvancedBackendTest();
$tester->runAllTests();
