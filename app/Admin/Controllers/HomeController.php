<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ContributionProgram;
use App\Models\ContributionProgramRecord;
use App\Models\Sacco;
use App\Models\User;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Widgets\Table;

class HomeController extends Controller
{
    /**
     * Display the SACCO Dashboard with comprehensive statistics
     * 
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        $user = Admin::user();
        
        // Get SACCO context
        $sacco = null;
        $sacco_id = null;
        
        // If user has a sacco_id, use it
        if ($user->sacco_id) {
            $sacco_id = $user->sacco_id;
            $sacco = Sacco::find($sacco_id);
        }
        
        // Build dashboard title
        $title = 'SACCO Dashboard';
        $description = 'Welcome back, ' . $user->name . '!';
        
        if ($sacco) {
            $title = $sacco->name . ' - Dashboard';
            $description = 'Comprehensive overview of your SACCO performance';
        }
        
        $content->title($title)->description($description);
        
        // =================================================================
        // QUICK ACTIONS: Reports
        // =================================================================
        $content->row(function (Row $row) use ($sacco_id) {
            $reportUrl = route('sacco.report');
            $treeUrl = route('family.tree');
            if ($sacco_id) {
                $reportUrl .= '?sacco_id=' . $sacco_id;
                $treeUrl .= '?sacco_id=' . $sacco_id;
            }
            
            $html = '
            <div style="margin-bottom: 15px; background: #f5f5f5; border: 1px solid #ddd; padding: 10px;">
                <div style="font-size: 11px; font-weight: bold; color: #333; margin-bottom: 8px;">📋 REPORTS</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <a href="' . $reportUrl . '" target="_blank" style="display: block; padding: 6px 12px; background: #333; color: #fff; text-decoration: none; font-size: 11px; text-align: center; border: 1px solid #333;">
                        📊 Comprehensive Report
                    </a>
                    <a href="' . $treeUrl . '" target="_blank" style="display: block; padding: 6px 12px; background: #333; color: #fff; text-decoration: none; font-size: 11px; text-align: center; border: 1px solid #333;">
                        🌳 Family Tree
                    </a>
                </div>
            </div>';
            
            $row->column(12, $html);
        });
        
        // =================================================================
        // ROW 1: KEY FINANCIAL METRICS
        // =================================================================
        $content->row(function (Row $row) use ($sacco_id) {
            // Members Statistics
            $row->column(3, function (Column $column) use ($sacco_id) {
                $query = User::query();
                if ($sacco_id) {
                    $query->where('sacco_id', $sacco_id);
                }
                
                $total_members = $query->count();
                $active_members = $query->where('status', 'Active')->count();
                $alive_members = $query->where('reg_number', 'Alive')->count();
                $eligible_contributors = $query->where('language', 'Compulsory')->count();
                
                $box = new InfoBox(
                    'Total Members',
                    'users',
                    'aqua',
                    admin_url('members'),
                    number_format($total_members)
                );
                $box->info = "<small>Active: $active_members | Alive: $alive_members | Contributors: $eligible_contributors</small>";
                $column->append($box);
            });
            
            // Contribution Programs
            $row->column(3, function (Column $column) use ($sacco_id) {
                $query = ContributionProgram::query();
                if ($sacco_id) {
                    $query->where('sacco_id', $sacco_id);
                }
                
                $total_programs = $query->count();
                $active_programs = $query->where('status', 'Active')->count();
                $total_expected = $query->sum('total_expected');
                
                $box = new InfoBox(
                    'Contribution Programs',
                    'calendar',
                    'green',
                    admin_url('contributions'),
                    number_format($total_programs)
                );
                $box->info = "<small>Active: $active_programs | Expected: UGX " . number_format($total_expected) . "</small>";
                $column->append($box);
            });
            
            // Total Expected Contributions
            $row->column(3, function (Column $column) use ($sacco_id) {
                $query = ContributionProgram::query();
                if ($sacco_id) {
                    $query->where('sacco_id', $sacco_id);
                }
                
                $total_expected = $query->sum('total_expected');
                $total_collected = $query->sum('total_collected');
                $collection_rate = $total_expected > 0 ? round(($total_collected / $total_expected) * 100, 1) : 0;
                
                $box = new InfoBox(
                    'Expected Contributions',
                    'money',
                    'yellow',
                    admin_url('contribution-program-records'),
                    'UGX ' . $this->formatMoney($total_expected)
                );
                $box->info = "<small>Collection Rate: {$collection_rate}%</small>";
                $column->append($box);
            });
            
            // Total Collected
            $row->column(3, function (Column $column) use ($sacco_id) {
                $query = ContributionProgram::query();
                if ($sacco_id) {
                    $query->where('sacco_id', $sacco_id);
                }
                
                $total_collected = $query->sum('total_collected');
                $total_balance = $query->sum('total_balance');
                
                $box = new InfoBox(
                    'Collected Contributions',
                    'check',
                    'green',
                    admin_url('contribution-program-records'),
                    'UGX ' . $this->formatMoney($total_collected)
                );
                $box->info = "<small>Balance: UGX " . $this->formatMoney($total_balance) . "</small>";
                $column->append($box);
            });
        });
        
        // =================================================================
        // ROW 2: CONTRIBUTION RECORDS & PAYMENT STATUS
        // =================================================================
        $content->row(function (Row $row) use ($sacco_id) {
            // Contribution Records Stats
            $row->column(3, function (Column $column) use ($sacco_id) {
                $query = ContributionProgramRecord::query();
                if ($sacco_id) {
                    $query->where('sacco_id', $sacco_id);
                }
                
                $total_records = $query->count();
                $paid_records = $query->where('is_paid', 'Yes')->count();
                $unpaid_records = $query->where('is_paid', 'No')->count();
                $payment_rate = $total_records > 0 ? round(($paid_records / $total_records) * 100, 1) : 0;
                
                $box = new InfoBox(
                    'Total Records',
                    'file-text',
                    'purple',
                    admin_url('contribution-program-records'),
                    number_format($total_records)
                );
                $box->info = "<small>Paid: $paid_records | Unpaid: $unpaid_records | Rate: {$payment_rate}%</small>";
                $column->append($box);
            });
            
            // Amount Expected from Records
            $row->column(3, function (Column $column) use ($sacco_id) {
                $query = ContributionProgramRecord::query();
                if ($sacco_id) {
                    $query->where('sacco_id', $sacco_id);
                }
                
                $total_amount = $query->sum('amount');
                $unpaid_amount = $query->where('is_paid', 'No')->sum('amount');
                
                $box = new InfoBox(
                    'Expected (Records)',
                    'calculator',
                    'red',
                    admin_url('contribution-program-records'),
                    'UGX ' . $this->formatMoney($total_amount)
                );
                $box->info = "<small>Unpaid: UGX " . $this->formatMoney($unpaid_amount) . "</small>";
                $column->append($box);
            });
            
            // Amount Paid
            $row->column(3, function (Column $column) use ($sacco_id) {
                $query = ContributionProgramRecord::query();
                if ($sacco_id) {
                    $query->where('sacco_id', $sacco_id);
                }
                
                $total_paid = $query->sum('paid_amount');
                $this_month_paid = $query->where('is_paid', 'Yes')
                    ->whereMonth('payment_date', Carbon::now()->month)
                    ->whereYear('payment_date', Carbon::now()->year)
                    ->sum('paid_amount');
                
                $box = new InfoBox(
                    'Paid Amount',
                    'check-circle',
                    'green',
                    admin_url('contribution-program-records'),
                    'UGX ' . $this->formatMoney($total_paid)
                );
                $box->info = "<small>This Month: UGX " . $this->formatMoney($this_month_paid) . "</small>";
                $column->append($box);
            });
            
            // Outstanding Balance
            $row->column(3, function (Column $column) use ($sacco_id) {
                $query = ContributionProgramRecord::query();
                if ($sacco_id) {
                    $query->where('sacco_id', $sacco_id);
                }
                
                $total_amount = $query->sum('amount');
                $total_paid = $query->sum('paid_amount');
                $balance = $total_amount - $total_paid;
                
                $overdue_records = $query->where('is_paid', 'No')
                    ->where('period_range_start', '<', Carbon::now())
                    ->count();
                
                $box = new InfoBox(
                    'Outstanding Balance',
                    'warning',
                    'red',
                    admin_url('contribution-program-records'),
                    'UGX ' . $this->formatMoney($balance)
                );
                $box->info = "<small>Overdue Records: " . number_format($overdue_records) . "</small>";
                $column->append($box);
            });
        });
        
        // =================================================================
        // ROW 3: RECENT ACTIVITY TABLES
        // =================================================================
        $content->row(function (Row $row) use ($sacco_id) {
            // Recent Contribution Programs
            $row->column(6, function (Column $column) use ($sacco_id) {
                $query = ContributionProgram::query();
                if ($sacco_id) {
                    $query->where('sacco_id', $sacco_id);
                }
                
                $programs = $query->orderBy('id', 'DESC')
                    ->limit(5)
                    ->get();
                
                $headers = ['Program', 'Type', 'Status', 'Expected', 'Collected', 'Balance'];
                $rows = [];
                
                foreach ($programs as $program) {
                    $collection_rate = $program->total_expected > 0 
                        ? round(($program->total_collected / $program->total_expected) * 100, 1) 
                        : 0;
                    
                    $status_color = $program->status == 'Active' ? 'success' : 'default';
                    
                    $rows[] = [
                        $program->name,
                        $program->contribution_type,
                        "<span class='label label-{$status_color}'>{$program->status}</span>",
                        'UGX ' . number_format($program->total_expected),
                        'UGX ' . number_format($program->total_collected) . " ({$collection_rate}%)",
                        'UGX ' . number_format($program->total_balance),
                    ];
                }
                
                $table = new Table($headers, $rows);
                $box = new Box('Recent Contribution Programs', $table);
                $box->style('success');
                $box->solid();
                $column->append($box);
            });
            
            // Recent Unpaid Contributions
            $row->column(6, function (Column $column) use ($sacco_id) {
                $query = ContributionProgramRecord::with(['member', 'program']);
                if ($sacco_id) {
                    $query->where('sacco_id', $sacco_id);
                }
                
                $records = $query->where('is_paid', 'No')
                    ->orderBy('period_range_start', 'ASC')
                    ->limit(10)
                    ->get();
                
                $headers = ['Member', 'Program', 'Period', 'Due Date', 'Amount'];
                $rows = [];
                
                foreach ($records as $record) {
                    $member_name = $record->member ? $record->member->name : 'N/A';
                    $program_name = $record->program ? $record->program->name : 'N/A';
                    $days_overdue = Carbon::parse($record->period_range_start)->diffInDays(Carbon::now(), false);
                    
                    $due_date_display = Carbon::parse($record->period_range_start)->format('d M Y');
                    if ($days_overdue > 0) {
                        $due_date_display .= " <span class='label label-danger'>{$days_overdue}d overdue</span>";
                    }
                    
                    $rows[] = [
                        $member_name,
                        $program_name,
                        $record->period_name,
                        $due_date_display,
                        'UGX ' . number_format($record->amount),
                    ];
                }
                
                $table = new Table($headers, $rows);
                $box = new Box('Unpaid Contributions (Oldest First)', $table);
                $box->style('danger');
                $box->solid();
                $column->append($box);
            });
        });
        
        // =================================================================
        // ROW 4: TOP CONTRIBUTORS & STATISTICS
        // =================================================================
        $content->row(function (Row $row) use ($sacco_id) {
            // Top Contributors (by payment)
            $row->column(6, function (Column $column) use ($sacco_id) {
                $query = ContributionProgramRecord::selectRaw('member_id, SUM(paid_amount) as total_paid, COUNT(*) as records_paid')
                    ->with('member')
                    ->where('is_paid', 'Yes');
                
                if ($sacco_id) {
                    $query->where('sacco_id', $sacco_id);
                }
                
                $top_contributors = $query->groupBy('member_id')
                    ->orderBy('total_paid', 'DESC')
                    ->limit(10)
                    ->get();
                
                $headers = ['#', 'Member', 'Records Paid', 'Total Paid'];
                $rows = [];
                $rank = 1;
                
                foreach ($top_contributors as $contributor) {
                    $member_name = $contributor->member ? $contributor->member->name : 'N/A';
                    
                    $medal = '';
                    if ($rank == 1) $medal = '🥇';
                    elseif ($rank == 2) $medal = '🥈';
                    elseif ($rank == 3) $medal = '🥉';
                    
                    $rows[] = [
                        $medal . ' ' . $rank,
                        $member_name,
                        number_format($contributor->records_paid),
                        'UGX ' . number_format($contributor->total_paid),
                    ];
                    $rank++;
                }
                
                $table = new Table($headers, $rows);
                $box = new Box('Top Contributors (By Amount Paid)', $table);
                $box->style('primary');
                $box->solid();
                $column->append($box);
            });
            
            // Monthly Contribution Trends
            $row->column(6, function (Column $column) use ($sacco_id) {
                $months = [];
                $expected_data = [];
                $collected_data = [];
                
                for ($i = 5; $i >= 0; $i--) {
                    $date = Carbon::now()->subMonths($i);
                    $month_name = $date->format('M Y');
                    $months[] = $month_name;
                    
                    $query = ContributionProgramRecord::query();
                    if ($sacco_id) {
                        $query->where('sacco_id', $sacco_id);
                    }
                    
                    $expected = $query->whereMonth('period_range_start', $date->month)
                        ->whereYear('period_range_start', $date->year)
                        ->sum('amount');
                    
                    $collected = $query->where('is_paid', 'Yes')
                        ->whereMonth('payment_date', $date->month)
                        ->whereYear('payment_date', $date->year)
                        ->sum('paid_amount');
                    
                    $expected_data[] = round($expected / 1000, 0); // Convert to thousands
                    $collected_data[] = round($collected / 1000, 0);
                }
                
                $headers = ['Month', 'Expected (K)', 'Collected (K)', 'Rate'];
                $rows = [];
                
                for ($i = 0; $i < count($months); $i++) {
                    $rate = $expected_data[$i] > 0 
                        ? round(($collected_data[$i] / $expected_data[$i]) * 100, 1) 
                        : 0;
                    
                    $rate_color = $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger');
                    
                    $rows[] = [
                        $months[$i],
                        number_format($expected_data[$i]),
                        number_format($collected_data[$i]),
                        "<span class='label label-{$rate_color}'>{$rate}%</span>",
                    ];
                }
                
                $table = new Table($headers, $rows);
                $box = new Box('6-Month Contribution Trends', $table);
                $box->style('info');
                $box->solid();
                $column->append($box);
            });
        });

        return $content;
    }
    
    /**
     * Format money values with K, M, B suffixes for large numbers
     * 
     * @param float $amount
     * @return string
     */
    private function formatMoney($amount)
    {
        if ($amount >= 1000000000) {
            return number_format($amount / 1000000000, 1) . 'B';
        } elseif ($amount >= 1000000) {
            return number_format($amount / 1000000, 1) . 'M';
        } elseif ($amount >= 1000) {
            return number_format($amount / 1000, 1) . 'K';
        }
        return number_format($amount);
    }
}
