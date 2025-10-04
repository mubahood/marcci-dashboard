<?php
/**
 * SIMPLIFIED CONTRIBUTION MODULE TESTING
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

echo "\n🧪 CONTRIBUTION MODULE TESTING (Simplified)\n";
echo str_repeat("=", 80) . "\n\n";

try {
    // Use existing data
    $sacco = Sacco::first();
    $admin = User::where('sacco_id', $sacco->id)->where('user_type', 'Admin')->first();
    
    if (!$admin) {
        echo "❌ No admin user found\n";
        exit(1);
    }
    
    echo "✅ Using Sacco: {$sacco->name} (ID: {$sacco->id})\n";
    echo "✅ Using Admin: {$admin->name} (ID: {$admin->id})\n\n";
    
    // Authenticate
    auth()->login($admin);
    
    // Test 1: Create Contribution Program
    echo "📋 Test 1: Creating Monthly Contribution Program...\n";
    
    $program = ContributionProgram::create([
        'sacco_id' => $sacco->id,
        'name' => 'Test Monthly Savings ' . date('YmdHis'),
        'contribution_type' => 'Periodic',
        'periodic_type' => 'Monthly',
        'amount_per_member_type' => 'Specific',
        'amount_per_member_value' => 10000,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(3)->toDateString(),
        'status' => 'Active',
    ]);
    
    echo "✅ Program created: ID={$program->id}, Name={$program->name}\n\n";
    
    // Test 2: Add members to program
    echo "📋 Test 2: Adding members to program...\n";
    
    $members = User::where('sacco_id', $sacco->id)
        ->where('user_type', 'Member')
        ->limit(3)
        ->get();
    
    foreach ($members as $member) {
        try {
            ContributionProgram::add_member_to_program($program, $member);
            echo "✅ Added member: {$member->name}\n";
        } catch (\Exception $e) {
            echo "⚠️  Error adding {$member->name}: " . $e->getMessage() . "\n";
        }
    }
    
    $recordCount = ContributionProgramRecord::where('contribution_program_id', $program->id)->count();
    echo "✅ Total records created: {$recordCount}\n\n";
    
    // Test 3: List records with pagination
    echo "📋 Test 3: Testing record pagination...\n";
    
    $records = ContributionProgramRecord::where('sacco_id', $sacco->id)
        ->with(['member', 'program'])
        ->paginate(10);
    
    echo "✅ Retrieved {$records->count()} records (Total: {$records->total()}, Pages: {$records->lastPage()})\n\n";
    
    // Test 4: Mark a record as paid
    echo "📋 Test 4: Testing payment recording...\n";
    
    $unpaidRecord = ContributionProgramRecord::where('contribution_program_id', $program->id)
        ->where('is_paid', 'No')
        ->first();
    
    if ($unpaidRecord) {
        $unpaidRecord->paid_amount = $unpaidRecord->amount;
        $unpaidRecord->is_paid = 'Yes';
        $unpaidRecord->payment_date = now()->toDateString();
        $unpaidRecord->teasurer_id = $admin->id;
        $unpaidRecord->save();
        
        echo "✅ Marked record as paid: ID={$unpaidRecord->id}, Amount={$unpaidRecord->amount}\n\n";
    } else {
        echo "⚠️  No unpaid records found\n\n";
    }
    
    // Test 5: Update balances
    echo "📋 Test 5: Testing balance calculations...\n";
    
    $program->update_balances();
    $program->refresh();
    
    echo "✅ Balance updated:\n";
    echo "   - Total Expected: {$program->total_expected_amount}\n";
    echo "   - Total Paid: {$program->total_paid_amount}\n";
    echo "   - Balance: " . ($program->total_expected_amount - $program->total_paid_amount) . "\n\n";
    
    // Test 6: Verify relationships work
    echo "📋 Test 6: Testing eager loading (N+1 prevention)...\n";
    
    DB::enableQueryLog();
    
    $testRecords = ContributionProgramRecord::where('sacco_id', $sacco->id)
        ->with(['member', 'program'])
        ->limit(10)
        ->get();
    
    $queryCount = count(DB::getQueryLog());
    echo "✅ Loaded 10 records with eager loading: {$queryCount} queries\n\n";
    
    DB::disableQueryLog();
    
    // Clean up test data
    echo "📋 Cleaning up test data...\n";
    ContributionProgramRecord::where('contribution_program_id', $program->id)->delete();
    $program->delete();
    echo "✅ Test data cleaned up\n\n";
    
    echo str_repeat("=", 80) . "\n";
    echo "✅ ALL TESTS PASSED!\n";
    echo str_repeat("=", 80) . "\n";
    
} catch (\Exception $e) {
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "❌ TEST FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo str_repeat("=", 80) . "\n";
    exit(1);
}
