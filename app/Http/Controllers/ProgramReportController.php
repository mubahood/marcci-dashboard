<?php

namespace App\Http\Controllers;

use App\Models\ContributionProgram;
use App\Models\ContributionProgramRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProgramReportController extends Controller
{
    /**
     * Generate detailed report for a contribution program
     *
     * @param int $program_id
     * @return \Illuminate\View\View
     */
    public function show($program_id)
    {
        // Get program with SACCO relationship
        $program = ContributionProgram::with('sacco')->findOrFail($program_id);
        
        // Get all records for this program
        $records = ContributionProgramRecord::with(['member', 'treasurer'])
            ->where('contribution_program_id', $program_id)
            ->orderBy('member_id', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();
        
        // Calculate program statistics
        $total_records = $records->count();
        $paid_records = $records->where('is_paid', 'Yes')->count();
        $unpaid_records = $records->where('is_paid', 'No')->count();
        $total_expected = $records->sum('amount');
        $total_paid = $records->sum('paid_amount');
        $total_balance = $total_expected - $total_paid;
        $payment_rate = $total_records > 0 ? round(($paid_records / $total_records) * 100, 1) : 0;
        
        // Get unique members count
        $total_members = $records->unique('member_id')->count();
        $active_members = $records->where('is_paid', 'Yes')->unique('member_id')->count();
        
        // Overdue contributions
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
            })
            ->sortBy('period_range_start');
        $upcoming_count = $upcoming_records->count();
        $upcoming_amount = $upcoming_records->sum('amount');
        
        // Recent payments (last 30 days)
        $recent_payments = $records->where('is_paid', 'Yes')
            ->filter(function($record) {
                return $record->payment_date && Carbon::parse($record->payment_date)->gte(Carbon::now()->subDays(30));
            })
            ->sortByDesc('payment_date')
            ->take(20);
        
        // Group records by member
        $members_with_records = [];
        $member_ids = $records->unique('member_id')->pluck('member_id');
        
        foreach ($member_ids as $member_id) {
            $member = User::find($member_id);
            if (!$member) continue;
            
            $member_records = $records->where('member_id', $member_id)->sortBy('id');
            $member_expected = $member_records->sum('amount');
            $member_paid = $member_records->sum('paid_amount');
            $member_balance = $member_expected - $member_paid;
            $member_paid_count = $member_records->where('is_paid', 'Yes')->count();
            $member_unpaid_count = $member_records->where('is_paid', 'No')->count();
            $member_payment_rate = $member_records->count() > 0 ? round(($member_paid_count / $member_records->count()) * 100, 1) : 0;
            
            $members_with_records[] = [
                'member' => $member,
                'records' => $member_records,
                'total_records' => $member_records->count(),
                'paid_count' => $member_paid_count,
                'unpaid_count' => $member_unpaid_count,
                'expected' => $member_expected,
                'paid' => $member_paid,
                'balance' => $member_balance,
                'payment_rate' => $member_payment_rate,
            ];
        }
        
        // Sort members by balance (highest first)
        usort($members_with_records, function($a, $b) {
            return $b['balance'] <=> $a['balance'];
        });
        
        // Top contributors (by amount paid)
        $top_contributors = collect($members_with_records)
            ->sortByDesc('paid')
            ->take(10)
            ->values();
        
        // Members with outstanding balance
        $defaulters = collect($members_with_records)
            ->filter(function($item) {
                return $item['balance'] > 0;
            })
            ->sortByDesc('balance')
            ->values();
        
        // Fully paid members
        $fully_paid = collect($members_with_records)
            ->filter(function($item) {
                return $item['balance'] == 0 && $item['total_records'] > 0;
            })
            ->sortBy('member.name')
            ->values();
        
        // Monthly collection trends
        $monthly_trends = [];
        $start_date = Carbon::parse($program->start_date ?? Carbon::now()->subMonths(11));
        $end_date = Carbon::parse($program->end_date ?? Carbon::now());
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            if ($date->lt($start_date) || $date->gt($end_date)) continue;
            
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
            
            if ($month_expected > 0 || $month_paid > 0) {
                $monthly_trends[] = [
                    'month' => $month_name,
                    'expected' => $month_expected,
                    'paid' => $month_paid,
                    'rate' => $month_expected > 0 ? round(($month_paid / $month_expected) * 100, 1) : 0
                ];
            }
        }
        
        return view('program-report', compact(
            'program',
            'records',
            'total_records',
            'paid_records',
            'unpaid_records',
            'total_expected',
            'total_paid',
            'total_balance',
            'payment_rate',
            'total_members',
            'active_members',
            'overdue_count',
            'overdue_amount',
            'overdue_records',
            'upcoming_count',
            'upcoming_amount',
            'upcoming_records',
            'recent_payments',
            'members_with_records',
            'top_contributors',
            'defaulters',
            'fully_paid',
            'monthly_trends'
        ));
    }
}
