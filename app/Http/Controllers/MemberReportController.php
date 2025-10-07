<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ContributionProgram;
use App\Models\ContributionProgramRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MemberReportController extends Controller
{
    /**
     * Generate detailed contribution report for a member
     *
     * @param int $user_id
     * @return \Illuminate\View\View
     */
    public function show($user_id)
    {
        // Get member with SACCO relationship
        $member = User::with('sacco')->findOrFail($user_id);
        
        // Get all contribution records for this member
        $records = ContributionProgramRecord::with(['program', 'treasurer'])
            ->where('member_id', $user_id)
            ->orderBy('period_range_start', 'DESC')
            ->get();
        
        // Get unique programs the member is enrolled in (only programs with actual records)
        $programs = ContributionProgram::whereHas('records', function($query) use ($user_id) {
            $query->where('member_id', $user_id);
        })
        ->withCount(['records' => function($query) use ($user_id) {
            $query->where('member_id', $user_id);
        }])
        ->having('records_count', '>', 0)
        ->orderBy('name', 'ASC')
        ->get();
        
        // Calculate summary statistics
        $total_records = $records->count();
        $paid_records = $records->where('is_paid', 'Yes')->count();
        $unpaid_records = $records->where('is_paid', 'No')->count();
        $total_expected = $records->sum('amount');
        $total_paid = $records->sum('paid_amount');
        $total_balance = $total_expected - $total_paid;
        $payment_rate = $total_records > 0 ? round(($paid_records / $total_records) * 100, 1) : 0;
        
        // Overdue contributions (sorted by oldest first)
        $overdue_records = $records->where('is_paid', 'No')
            ->filter(function($record) {
                return Carbon::parse($record->period_range_start)->isPast();
            })
            ->sortBy('period_range_start');
        $overdue_count = $overdue_records->count();
        $overdue_amount = $overdue_records->sum('amount');
        
        // Upcoming contributions (next 30 days)
        $upcoming_records = $records->where('is_paid', 'No')
            ->filter(function($record) {
                $due_date = Carbon::parse($record->period_range_start);
                return $due_date->isFuture() && $due_date->lte(Carbon::now()->addDays(30));
            });
        $upcoming_count = $upcoming_records->count();
        $upcoming_amount = $upcoming_records->sum('amount');
        
        // Recent payments (last 6 months)
        $recent_payments = $records->where('is_paid', 'Yes')
            ->filter(function($record) {
                return $record->payment_date && Carbon::parse($record->payment_date)->gte(Carbon::now()->subMonths(6));
            })
            ->sortByDesc('payment_date')
            ->take(10);
        
        // Monthly contribution trends (last 12 months)
        $monthly_trends = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month_key = $date->format('Y-m');
            $month_name = $date->format('M Y');
            
            $month_expected = $records->filter(function($record) use ($date) {
                $period_date = Carbon::parse($record->period_range_start);
                return $period_date->year == $date->year && $period_date->month == $date->month;
            })->sum('amount');
            
            $month_paid = $records->filter(function($record) use ($date) {
                if ($record->is_paid == 'Yes' && $record->payment_date) {
                    $payment_date = Carbon::parse($record->payment_date);
                    return $payment_date->year == $date->year && $payment_date->month == $date->month;
                }
                return false;
            })->sum('paid_amount');
            
            $monthly_trends[] = [
                'month' => $month_name,
                'expected' => $month_expected,
                'paid' => $month_paid,
                'rate' => $month_expected > 0 ? round(($month_paid / $month_expected) * 100, 1) : 0
            ];
        }
        
        // Program-wise summary (sorted by name)
        $program_summary = [];
        foreach ($programs as $program) {
            $program_records = $records->where('contribution_program_id', $program->id);
            
            // Skip if no records
            if ($program_records->count() == 0) {
                continue;
            }
            
            $program_paid = $program_records->where('is_paid', 'Yes')->count();
            $program_unpaid = $program_records->where('is_paid', 'No')->count();
            $program_expected = $program_records->sum('amount');
            $program_paid_amount = $program_records->sum('paid_amount');
            $program_balance = $program_expected - $program_paid_amount;
            
            $program_summary[] = [
                'program' => $program,
                'total_records' => $program_records->count(),
                'paid_records' => $program_paid,
                'unpaid_records' => $program_unpaid,
                'expected' => $program_expected,
                'paid_amount' => $program_paid_amount,
                'balance' => $program_balance,
                'payment_rate' => $program_records->count() > 0 ? round(($program_paid / $program_records->count()) * 100, 1) : 0
            ];
        }
        
        // Sort program summary by balance (highest first)
        usort($program_summary, function($a, $b) {
            return $b['balance'] <=> $a['balance'];
        });
        
        // Group records by program for organized display
        // Sort programs by ID DESC, records within each program by ID ASC
        $records_by_program = [];
        $programs_with_records = ContributionProgram::whereHas('records', function($query) use ($user_id) {
            $query->where('member_id', $user_id);
        })
        ->orderBy('id', 'DESC')
        ->get();
        
        foreach ($programs_with_records as $program) {
            $program_records = ContributionProgramRecord::with(['program', 'treasurer'])
                ->where('member_id', $user_id)
                ->where('contribution_program_id', $program->id)
                ->orderBy('id', 'ASC')
                ->get();
            
            if ($program_records->count() > 0) {
                $records_by_program[] = [
                    'program' => $program,
                    'records' => $program_records,
                    'total_expected' => $program_records->sum('amount'),
                    'total_paid' => $program_records->sum('paid_amount'),
                    'total_balance' => $program_records->sum('amount') - $program_records->sum('paid_amount'),
                ];
            }
        }
        
        return view('member-report', compact(
            'member',
            'records',
            'programs',
            'total_records',
            'paid_records',
            'unpaid_records',
            'total_expected',
            'total_paid',
            'total_balance',
            'payment_rate',
            'overdue_count',
            'overdue_amount',
            'upcoming_count',
            'upcoming_amount',
            'recent_payments',
            'monthly_trends',
            'program_summary',
            'overdue_records',
            'records_by_program'
        ));
    }
}
