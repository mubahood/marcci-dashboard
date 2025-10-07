<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Report - {{ $program->name }}</title>
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

        @media print {
            body { background: white; padding: 0; }
            .container { padding: 10px; max-width: 100%; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }

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
            color: #666;
        }

        .program-info {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 12px;
            margin-bottom: 15px;
        }

        .program-info h3 {
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

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
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

        .section-header {
            background: #333;
            color: white;
            padding: 6px 10px;
            margin: 15px 0 10px 0;
            font-size: 13px;
            font-weight: bold;
        }

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

        .report-footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 11px;
        }

        .member-section {
            margin-bottom: 20px;
        }

        .member-header {
            background: #f9f9f9;
            border-left: 3px solid #333;
            padding: 8px 12px;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 12px;
        }

        .member-stats {
            float: right;
            color: #666;
            font-weight: normal;
        }

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
        <!-- Action Buttons -->
        <div class="action-buttons no-print">
            <button class="btn" onclick="window.print()">Print Report</button>
            <button class="btn" onclick="copyReport()">Copy</button>
            <a href="{{ url('admin/contributions') }}" class="btn">Back</a>
        </div>

        <!-- Report Header -->
        <div class="report-header">
            <h1>Contribution Program Report</h1>
            <h2>{{ $program->sacco ? $program->sacco->name : 'SACCO Management System' }}</h2>
            <div class="report-meta">
                <div><strong>Date:</strong> {{ \Carbon\Carbon::now()->format('d M Y') }}</div>
                <div><strong>By:</strong> {{ Auth::user()->name ?? 'System' }}</div>
                <div><strong>ID:</strong> #{{ str_pad($program->id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        <!-- Program Information -->
        <div class="program-info">
            <h3>Program Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Program Name</span>
                    <span class="info-value">{{ $program->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Type</span>
                    <span class="info-value">{{ $program->contribution_type }}</span>
                </div>
                @if($program->contribution_type == 'Periodic')
                <div class="info-item">
                    <span class="info-label">Period</span>
                    <span class="info-value">{{ $program->periodic_type ?? 'N/A' }}</span>
                </div>
                @endif
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        <span class="label {{ $program->status == 'Active' ? 'label-success' : 'label-default' }}">
                            {{ $program->status }}
                        </span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Start Date</span>
                    <span class="info-value">{{ $program->start_date ? \Carbon\Carbon::parse($program->start_date)->format('d M Y') : 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">End Date</span>
                    <span class="info-value">{{ $program->end_date ? \Carbon\Carbon::parse($program->end_date)->format('d M Y') : 'N/A' }}</span>
                </div>
                @if($program->target_amount)
                <div class="info-item">
                    <span class="info-label">Target Amount</span>
                    <span class="info-value">UGX {{ number_format($program->target_amount) }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <h4>Total Members</h4>
                <div class="value">{{ number_format($total_members) }}</div>
                <div class="subtext">{{ $active_members }} active</div>
            </div>
            <div class="summary-card">
                <h4>Total Records</h4>
                <div class="value">{{ number_format($total_records) }}</div>
                <div class="subtext">{{ $paid_records }}/{{ $unpaid_records }}</div>
            </div>
            <div class="summary-card">
                <h4>Total Expected</h4>
                <div class="value">{{ number_format($total_expected) }}</div>
                <div class="subtext">All periods</div>
            </div>
            <div class="summary-card">
                <h4>Total Paid</h4>
                <div class="value">{{ number_format($total_paid) }}</div>
                <div class="subtext">{{ $payment_rate }}%</div>
            </div>
            <div class="summary-card">
                <h4>Balance Due</h4>
                <div class="value">{{ number_format($total_balance) }}</div>
                <div class="subtext">{{ $overdue_count }} overdue</div>
            </div>
        </div>

        <!-- Members Summary -->
        <div class="section-header">Members Summary ({{ count($members_with_records) }} members)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member Name</th>
                    <th>Phone</th>
                    <th>Records</th>
                    <th>Expected</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($members_with_records as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $item['member']->name }}</strong></td>
                    <td>{{ $item['member']->phone_number ?? '-' }}</td>
                    <td>{{ $item['total_records'] }}</td>
                    <td>{{ number_format($item['expected']) }}</td>
                    <td>{{ number_format($item['paid']) }}</td>
                    <td><strong>{{ number_format($item['balance']) }}</strong></td>
                    <td>
                        <span class="label {{ $item['payment_rate'] >= 80 ? 'label-success' : ($item['payment_rate'] >= 50 ? 'label-warning' : 'label-danger') }}">
                            {{ $item['payment_rate'] }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Top Contributors -->
        @if($top_contributors->count() > 0)
        <div class="section-header">Top Contributors ({{ $top_contributors->count() }})</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Member Name</th>
                    <th>Records Paid</th>
                    <th>Total Paid</th>
                    <th>Payment Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($top_contributors as $index => $item)
                <tr>
                    <td>
                        @if($index == 0) 🥇
                        @elseif($index == 1) 🥈
                        @elseif($index == 2) 🥉
                        @else {{ $index + 1 }}
                        @endif
                    </td>
                    <td><strong>{{ $item['member']->name }}</strong></td>
                    <td>{{ $item['paid_count'] }}/{{ $item['total_records'] }}</td>
                    <td><strong>{{ number_format($item['paid']) }}</strong></td>
                    <td>
                        <span class="label label-success">{{ $item['payment_rate'] }}%</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Defaulters (Members with Outstanding Balance) -->
        @if($defaulters->count() > 0)
        <div class="section-header">Outstanding Balances ({{ $defaulters->count() }} members)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member Name</th>
                    <th>Phone</th>
                    <th>Expected</th>
                    <th>Paid</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($defaulters as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $item['member']->name }}</strong></td>
                    <td>{{ $item['member']->phone_number ?? '-' }}</td>
                    <td>{{ number_format($item['expected']) }}</td>
                    <td>{{ number_format($item['paid']) }}</td>
                    <td><strong>{{ number_format($item['balance']) }}</strong></td>
                </tr>
                @endforeach
                <tr style="background: #f5f5f5; font-weight: bold;">
                    <td colspan="5" style="text-align: right;">TOTAL OUTSTANDING:</td>
                    <td><strong>UGX {{ number_format($defaulters->sum('balance')) }}</strong></td>
                </tr>
            </tbody>
        </table>
        @endif

        <!-- Fully Paid Members -->
        @if($fully_paid->count() > 0)
        <div class="section-header">Fully Paid Members ({{ $fully_paid->count() }})</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member Name</th>
                    <th>Phone</th>
                    <th>Records</th>
                    <th>Total Paid</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fully_paid as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $item['member']->name }}</strong></td>
                    <td>{{ $item['member']->phone_number ?? '-' }}</td>
                    <td>{{ $item['total_records'] }}</td>
                    <td>{{ number_format($item['paid']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <div class="page-break"></div>

        <!-- Overdue Contributions -->
        @if($overdue_count > 0)
        <div class="section-header">Overdue Contributions ({{ $overdue_count }})</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member</th>
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
                    <td>{{ $record->member ? $record->member->name : 'N/A' }}</td>
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

        <!-- Recent Payments -->
        @if($recent_payments->count() > 0)
        <div class="section-header">Recent Payments ({{ $recent_payments->count() }})</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Member</th>
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
                    <td>{{ $payment->member ? $payment->member->name : 'N/A' }}</td>
                    <td>{{ $payment->period_name }}</td>
                    <td><strong>{{ number_format($payment->paid_amount) }}</strong></td>
                    <td>{{ $payment->treasurer ? $payment->treasurer->name : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Detailed Member Records -->
        <div class="section-header">Detailed Records by Member ({{ count($members_with_records) }} members)</div>
        @foreach($members_with_records as $item)
        <div class="member-section">
            <div class="member-header">
                {{ $item['member']->name }}
                <span class="member-stats">
                    {{ $item['total_records'] }} records | 
                    Expected: {{ number_format($item['expected']) }} | 
                    Paid: {{ number_format($item['paid']) }} | 
                    Balance: <strong>{{ number_format($item['balance']) }}</strong>
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
                    @foreach($item['records'] as $record)
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
            <span>PROGRAM TOTALS:</span>
            <span style="float: right;">
                Members: {{ $total_members }} | 
                Records: {{ $total_records }} | 
                Expected: UGX {{ number_format($total_expected) }} | 
                Paid: UGX {{ number_format($total_paid) }} | 
                Balance: UGX {{ number_format($total_balance) }}
            </span>
        </div>

        <!-- Report Footer -->
        <div class="report-footer">
            <p><strong>{{ $program->sacco ? $program->sacco->name : 'SACCO Management System' }}</strong></p>
            <p>This is a system-generated report. For any discrepancies, please contact your SACCO administrator.</p>
            <p>Generated on {{ \Carbon\Carbon::now()->format('l, d F Y \a\t h:i A') }}</p>
        </div>
    </div>

    <script>
        function copyReport() {
            const reportContent = document.querySelector('.container').cloneNode(true);
            const noPrintElements = reportContent.querySelectorAll('.no-print');
            noPrintElements.forEach(el => el.remove());
            const textContent = reportContent.innerText;
            navigator.clipboard.writeText(textContent).then(() => {
                alert('✅ Report copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy: ', err);
                alert('❌ Failed to copy report. Please try again.');
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>
