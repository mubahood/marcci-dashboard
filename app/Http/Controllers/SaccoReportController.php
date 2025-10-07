<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sacco;
use App\Models\ContributionProgram;
use App\Models\ContributionProgramRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Encore\Admin\Facades\Admin;

class SaccoReportController extends Controller
{
    /**
     * Generate comprehensive SACCO/Family report
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function show(Request $request)
    {
        $user = Admin::user();
        
        // Get SACCO based on user or request parameter
        // If user has sacco_id, use it. Otherwise, allow sacco_id from request
        if ($user->sacco_id) {
            $sacco_id = $user->sacco_id;
        } else {
            $sacco_id = $request->get('sacco_id', null);
        }
        
        if (!$sacco_id) {
            // If no SACCO specified, show all SACCOs combined
            $sacco = null;
        } else {
            $sacco = Sacco::find($sacco_id);
        }
        
        // =================================================================
        // POPULATION STATISTICS
        // =================================================================
        
        $members_query = User::query();
        if ($sacco_id) {
            $members_query->where('sacco_id', $sacco_id);
        }
        
        // Clone query for different metrics
        $total_members = (clone $members_query)->count();
        $active_members = (clone $members_query)->where('status', 'Active')->count();
        $alive_members = (clone $members_query)->where('reg_number', 'Alive')->count();
        $deceased_members = (clone $members_query)->where('reg_number', 'Late')->count();
        $male_members = (clone $members_query)->where('sex', 'Male')->count();
        $female_members = (clone $members_query)->where('sex', 'Female')->count();
        
        // Age group analysis
        $age_groups = [
            '0-17' => (clone $members_query)->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 18')->count(),
            '18-35' => (clone $members_query)->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 18 AND 35')->count(),
            '36-50' => (clone $members_query)->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 36 AND 50')->count(),
            '51-65' => (clone $members_query)->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 51 AND 65')->count(),
            '66+' => (clone $members_query)->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) > 65')->count(),
        ];
        
        // =================================================================
        // FINANCIAL SUMMARIES
        // =================================================================
        
        $programs_query = ContributionProgram::query();
        if ($sacco_id) {
            $programs_query->where('sacco_id', $sacco_id);
        }
        
        $total_expected = (clone $programs_query)->sum('total_expected');
        $total_collected = (clone $programs_query)->sum('total_collected');
        $total_balance = (clone $programs_query)->sum('total_balance');
        $collection_rate = $total_expected > 0 ? round(($total_collected / $total_expected) * 100, 1) : 0;
        
        // =================================================================
        // PROGRAM STATISTICS
        // =================================================================
        
        $total_programs = (clone $programs_query)->count();
        $active_programs = (clone $programs_query)->where('status', 'Active')->count();
        
        // =================================================================
        // RECORD STATISTICS
        // =================================================================
        
        $records_query = ContributionProgramRecord::query();
        if ($sacco_id) {
            $records_query->where('sacco_id', $sacco_id);
        }
        
        $total_records = (clone $records_query)->count();
        $paid_records = (clone $records_query)->where('is_paid', 'Yes')->count();
        $unpaid_records = (clone $records_query)->where('is_paid', 'No')->count();
        $payment_rate = $total_records > 0 ? round(($paid_records / $total_records) * 100, 1) : 0;
        
        // =================================================================
        // OVERDUE ANALYSIS
        // =================================================================
        
        $overdue_records = (clone $records_query)
            ->where('is_paid', 'No')
            ->where('period_range_start', '<', Carbon::now())
            ->get();
        
        $overdue_count = $overdue_records->count();
        $overdue_amount = $overdue_records->sum('amount');
        
        // =================================================================
        // TOP CONTRIBUTORS
        // =================================================================
        
        $top_contributors_query = ContributionProgramRecord::selectRaw('member_id, SUM(paid_amount) as total_paid, COUNT(*) as record_count')
            ->with('member')
            ->where('is_paid', 'Yes');
        
        if ($sacco_id) {
            $top_contributors_query->where('sacco_id', $sacco_id);
        }
        
        $top_contributors = $top_contributors_query
            ->groupBy('member_id')
            ->orderBy('total_paid', 'DESC')
            ->limit(10)
            ->get();
        
        // =================================================================
        // RECENT ACTIVITY (LAST 30 DAYS)
        // =================================================================
        
        $recent_payments_query = ContributionProgramRecord::with(['member', 'program'])
            ->where('is_paid', 'Yes')
            ->where('payment_date', '>=', Carbon::now()->subDays(30));
        
        if ($sacco_id) {
            $recent_payments_query->where('sacco_id', $sacco_id);
        }
        
        $recent_payments = $recent_payments_query
            ->orderBy('payment_date', 'DESC')
            ->limit(20)
            ->get();
        
        // =================================================================
        // 12-MONTH TRENDS
        // =================================================================
        
        $monthly_trends = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month_name = $date->format('M Y');
            
            $records_query_month = ContributionProgramRecord::query();
            if ($sacco_id) {
                $records_query_month->where('sacco_id', $sacco_id);
            }
            
            $expected = (clone $records_query_month)
                ->whereMonth('period_range_start', $date->month)
                ->whereYear('period_range_start', $date->year)
                ->sum('amount');
            
            $paid = (clone $records_query_month)
                ->where('is_paid', 'Yes')
                ->whereMonth('payment_date', $date->month)
                ->whereYear('payment_date', $date->year)
                ->sum('paid_amount');
            
            $monthly_trends[] = [
                'month' => $month_name,
                'expected' => $expected,
                'paid' => $paid,
                'rate' => $expected > 0 ? round(($paid / $expected) * 100, 1) : 0
            ];
        }
        
        // =================================================================
        // RETURN VIEW WITH ALL DATA
        // =================================================================
        
        return view('sacco-report', compact(
            'sacco',
            'total_members',
            'active_members',
            'alive_members',
            'deceased_members',
            'male_members',
            'female_members',
            'age_groups',
            'total_expected',
            'total_collected',
            'total_balance',
            'collection_rate',
            'total_programs',
            'active_programs',
            'total_records',
            'paid_records',
            'unpaid_records',
            'payment_rate',
            'overdue_count',
            'overdue_amount',
            'top_contributors',
            'recent_payments',
            'monthly_trends'
        ));
    }
}
