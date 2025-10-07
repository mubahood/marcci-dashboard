<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contribution Report - {{ $member->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #fff;
            padding: 10px;
            color: #333;
            font-size: 13px;
            line-height: 1.4;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 15px;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .container {
                padding: 10px;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
        }

        /* Header Section */
        .report-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .report-header h1 {
            color: #333;
            font-size: 20px;
            margin-bottom: 5px;
        }

        .report-header h2 {
            color: #666;
            font-size: 14px;
            font-weight: normal;
        }

        .report-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            font-size: 11px;
        }

        .report-meta div {
            color: #666;
        }

        /* Member Info Card */
        .member-info {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 12px;
            margin-bottom: 15px;
        }

        .member-info h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 10px;
            color: #666;
            margin-bottom: 3px;
        }

        .info-value {
            font-size: 12px;
            font-weight: bold;
            color: #333;
        }

        /* Summary Cards */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }

        .summary-card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
        }

        .summary-card h4 {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .summary-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
        }

        .summary-card .subtext {
            font-size: 10px;
            color: #999;
        }

        /* Section Headers */
        .section-header {
            background: #333;
            color: white;
            padding: 6px 10px;
            margin: 15px 0 10px 0;
            font-size: 13px;
            font-weight: bold;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            font-size: 11px;
        }

        .data-table thead {
            background: #f5f5f5;
            color: #333;
        }

        .data-table th {
            padding: 6px 8px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }

        .data-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #eee;
        }

        .data-table tbody tr:hover {
            background: #fafafa;
        }
        
        .data-table tfoot tr,
        .data-table tbody tr.total-row {
            background: #f5f5f5 !important;
            font-weight: bold;
            border-top: 2px solid #333;
        }

        /* Status Labels */
        .label {
            display: inline-block;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
            border: 1px solid #ddd;
        }

        .label-success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .label-danger {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .label-warning {
            background: #fff3cd;
            color: #856404;
            border-color: #ffeeba;
        }

        .label-info {
            background: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }

        .label-default {
            background: #e9ecef;
            color: #495057;
            border-color: #dee2e6;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
        }

        .btn {
            padding: 6px 12px;
            border: 1px solid #ddd;
            background: #fff;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
            color: #333;
        }

        .btn:hover {
            background: #f5f5f5;
        }

        /* Trends Chart */
        .trends-chart {
            margin-bottom: 15px;
        }

        .chart-bar {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .chart-label {
            width: 60px;
            font-size: 10px;
            color: #666;
        }

        .chart-bars {
            flex: 1;
            display: flex;
            gap: 3px;
            align-items: center;
        }

        .bar {
            height: 18px;
            background: #ddd;
            display: flex;
            align-items: center;
            padding: 0 5px;
            color: #333;
            font-size: 9px;
            font-weight: bold;
        }

        .bar.paid {
            background: #d4edda;
        }

        .bar.expected {
            background: #fff3cd;
        }

        .chart-value {
            width: 60px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 20px;
            color: #999;
            font-size: 12px;
        }

        /* Footer */
        .report-footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 11px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .summary-cards {
                grid-template-columns: 1fr 1fr;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Action Buttons (No Print) -->
        <div class="action-buttons no-print">
            <button class="btn" onclick="window.print()">Print Report</button>
            <button class="btn" onclick="copyReport()">Copy</button>
            <a href="{{ url('admin/members') }}" class="btn">Back</a>
        </div>

        <!-- Report Header -->
        <div class="report-header">
            <h1>Member Contribution Report</h1>
            <h2>{{ $member->sacco ? $member->sacco->name : 'SACCO Management System' }}</h2>
            <div class="report-meta">
                <div><strong>Date:</strong> {{ \Carbon\Carbon::now()->format('d M Y') }}</div>
                <div><strong>By:</strong> {{ Auth::user()->name ?? 'System' }}</div>
                <div><strong>ID:</strong> #{{ str_pad($member->id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        <!-- Member Information -->
        <div class="member-info">
            <h3>Member Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Full Name</span>
                    <span class="info-value">{{ $member->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Member ID</span>
                    <span class="info-value">{{ $member->id }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone Number</span>
                    <span class="info-value">{{ $member->phone_number ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ $member->email ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        <span class="label {{ $member->status == 'Active' ? 'label-success' : 'label-default' }}">
                            {{ $member->status ?? 'N/A' }}
                        </span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Member Since</span>
                    <span class="info-value">{{ $member->created_at ? $member->created_at->format('d M Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <h4>Total Records</h4>
                <div class="value">{{ number_format($total_records) }}</div>
                <div class="subtext">{{ $paid_records }} paid, {{ $unpaid_records }} unpaid</div>
            </div>
            <div class="summary-card">
                <h4>Total Expected</h4>
                <div class="value">UGX {{ number_format($total_expected) }}</div>
                <div class="subtext">All programs</div>
            </div>
            <div class="summary-card">
                <h4>Total Paid</h4>
                <div class="value">UGX {{ number_format($total_paid) }}</div>
                <div class="subtext">Rate: {{ $payment_rate }}%</div>
            </div>
            <div class="summary-card">
                <h4>Balance Due</h4>
                <div class="value">UGX {{ number_format($total_balance) }}</div>
                <div class="subtext">{{ $overdue_count }} overdue</div>
            </div>
        </div>

        <!-- Program Summary -->
        @if(count($program_summary) > 0)
        <div class="section-header">Contribution Programs ({{ count($program_summary) }})</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Program Name</th>
                    <th>Records</th>
                    <th>Expected</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($program_summary as $index => $summary)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $summary['program']->name }}</strong></td>
                    <td>{{ $summary['total_records'] }}</td>
                    <td>{{ number_format($summary['expected']) }}</td>
                    <td>{{ number_format($summary['paid_amount']) }}</td>
                    <td><strong>{{ number_format($summary['balance']) }}</strong></td>
                    <td>
                        <span class="label {{ $summary['payment_rate'] >= 80 ? 'label-success' : ($summary['payment_rate'] >= 50 ? 'label-warning' : 'label-danger') }}">
                            {{ $summary['payment_rate'] }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Overdue Contributions -->
        @if($overdue_count > 0)
        <div class="section-header">Overdue Contributions ({{ $overdue_count }})</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Program</th>
                    <th>Period</th>
                    <th>Due Date</th>
                    <th>Days</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($overdue_records as $index => $record)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $record->program ? $record->program->name : 'N/A' }}</td>
                    <td>{{ $record->period_name }}</td>
                    <td>{{ \Carbon\Carbon::parse($record->period_range_start)->format('d M Y') }}</td>
                    <td><span class="label label-danger">{{ \Carbon\Carbon::parse($record->period_range_start)->diffInDays(\Carbon\Carbon::now()) }}d</span></td>
                    <td><strong>{{ number_format($record->amount) }}</strong></td>
                </tr>
                @endforeach
                <tr style="background: #f5f5f5; font-weight: bold;">
                    <td colspan="5" style="text-align: right;">TOTAL OVERDUE:</td>
                    <td><strong>UGX {{ number_format($overdue_amount) }}</strong></td>
                </tr>
            </tbody>
        </table>
        @endif

        <!-- Page Break for Print -->
        <div class="page-break"></div>

        <!-- Recent Payments -->
        @if($recent_payments->count() > 0)
        <div class="section-header">Recent Payments ({{ $recent_payments->count() }})</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Program</th>
                    <th>Period</th>
                    <th>Amount</th>
                    <th>Received By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recent_payments as $index => $payment)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') : 'N/A' }}</td>
                    <td>{{ $payment->program ? $payment->program->name : 'N/A' }}</td>
                    <td>{{ $payment->period_name }}</td>
                    <td><strong>{{ number_format($payment->paid_amount) }}</strong></td>
                    <td>{{ $payment->treasurer ? $payment->treasurer->name : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- All Contribution Records -->
        @if($records->count() > 0)
        <div class="section-header">All Contribution Records ({{ $records->count() }} records, {{ count($records_by_program) }} programs)</div>
        
        @foreach($records_by_program as $group)
        <div style="margin-bottom: 20px;">
            <div style="background: #f9f9f9; border-left: 3px solid #333; padding: 8px 12px; margin-bottom: 8px; font-weight: bold; font-size: 12px;">
                {{ $group['program']->name }}
                <span style="float: right; color: #666; font-weight: normal;">
                    {{ $group['records']->count() }} records | 
                    Expected: {{ number_format($group['total_expected']) }} | 
                    Paid: {{ number_format($group['total_paid']) }} | 
                    Balance: <strong>{{ number_format($group['total_balance']) }}</strong>
                </span>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Period</th>
                        <th>Due Date</th>
                        <th>Expected</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group['records'] as $record)
                    <tr>
                        <td>{{ $record->id }}</td>
                        <td>{{ $record->period_name }}</td>
                        <td>
                            {{ $record->period_range_start ? \Carbon\Carbon::parse($record->period_range_start)->format('d M Y') : 'N/A' }}
                            @if($record->is_paid == 'No' && $record->period_range_start && \Carbon\Carbon::parse($record->period_range_start)->isPast())
                                <br><span class="label label-danger">{{ \Carbon\Carbon::parse($record->period_range_start)->diffInDays(\Carbon\Carbon::now()) }}d</span>
                            @endif
                        </td>
                        <td>{{ number_format($record->amount) }}</td>
                        <td>{{ number_format($record->paid_amount) }}</td>
                        <td><strong>{{ number_format($record->amount - $record->paid_amount) }}</strong></td>
                        <td>
                            <span class="label {{ $record->is_paid == 'Yes' ? 'label-success' : 'label-danger' }}">
                                {{ $record->is_paid == 'Yes' ? 'PAID' : 'UNPAID' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
        
        <!-- Grand Total -->
        <div style="background: #333; color: white; padding: 10px; margin-top: 15px; font-weight: bold;">
            <span>GRAND TOTAL (All Programs):</span>
            <span style="float: right;">
                Expected: UGX {{ number_format($total_expected) }} | 
                Paid: UGX {{ number_format($total_paid) }} | 
                Balance: UGX {{ number_format($total_balance) }}
            </span>
        </div>
        @endif

        <!-- Report Footer -->
        <div class="report-footer">
            <p><strong>{{ $member->sacco ? $member->sacco->name : 'SACCO Management System' }}</strong></p>
            <p>This is a system-generated report. For any discrepancies, please contact your SACCO administrator.</p>
            <p>Generated on {{ \Carbon\Carbon::now()->format('l, d F Y \a\t h:i A') }}</p>
        </div>
    </div>

    <script>
        function copyReport() {
            // Create a temporary element to hold the report content
            const reportContent = document.querySelector('.container').cloneNode(true);
            
            // Remove no-print elements
            const noPrintElements = reportContent.querySelectorAll('.no-print');
            noPrintElements.forEach(el => el.remove());
            
            // Get text content
            const textContent = reportContent.innerText;
            
            // Copy to clipboard
            navigator.clipboard.writeText(textContent).then(() => {
                alert('✅ Report copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy: ', err);
                alert('❌ Failed to copy report. Please try again.');
            });
        }

        // Print on Ctrl+P
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>
