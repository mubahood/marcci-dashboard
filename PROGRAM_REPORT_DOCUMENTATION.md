# Program Report Documentation

## Overview
The Program Report provides a comprehensive analysis of a contribution program, including member participation, payment statistics, and detailed records.

## Features

### 1. Program Information
- Program Name, Type, Period
- Status, Start Date, End Date
- Target Amount (if applicable)

### 2. Summary Statistics
- **Total Members**: Count of enrolled members
- **Total Records**: All contribution records
- **Total Expected**: Sum of all expected contributions
- **Total Paid**: Sum of all payments received
- **Balance Due**: Outstanding amount
- **Payment Rate**: Percentage of paid records

### 3. Members Summary Table
Complete list of all enrolled members with:
- Member name and phone
- Number of records
- Expected amount
- Paid amount
- Balance
- Payment rate (%)

**Sorting**: By balance (highest first)

### 4. Top Contributors
Top 10 members by total amount paid with:
- Rank (🥇🥈🥉 medals for top 3)
- Member name
- Records paid vs total
- Total amount paid
- Payment rate

### 5. Outstanding Balances
Members with unpaid balances:
- Member details
- Expected vs Paid amounts
- Balance due
- **Total Outstanding** summary row

**Sorting**: By balance (highest first)

### 6. Fully Paid Members
Members with zero balance:
- Member name and phone
- Number of records paid
- Total amount paid

**Sorting**: Alphabetically by name

### 7. Overdue Contributions
All unpaid records past due date:
- Member name
- Period name
- Due date
- Days overdue (with label)
- Amount due
- **Total Overdue** summary row

**Sorting**: By due date (oldest first)

### 8. Recent Payments
Last 30 days of payments:
- Payment date
- Member name
- Period
- Amount paid
- Received by (treasurer)

**Sorting**: By payment date (newest first)

### 9. Detailed Member Records
Complete breakdown by member:
- Each member has own section
- Section header shows member summary
- Table with all contribution records
- Records sorted by ID (ascending)

**Member Sections Sorted**: By balance (highest first)

## Access

### URL Pattern
```
/program-report/{program_id}
```

### Example
```
http://localhost:8888/marcci-dashboard/program-report/123
```

### From Admin Panel
1. Go to **Contributions** menu
2. Find desired program in grid
3. Click **"Print Report"** button in Report column
4. Report opens in new tab

## Actions

### Available Buttons
- **Print Report**: Opens print dialog (Ctrl+P)
- **Copy**: Copies report text to clipboard
- **Back**: Returns to Contributions list

## Report Sections Order

1. Program Information (card)
2. Summary Statistics (5 cards)
3. Members Summary (all members)
4. Top Contributors (top 10)
5. Outstanding Balances (members with balance)
6. Fully Paid Members (zero balance)
7. Overdue Contributions (past due)
8. Recent Payments (last 30 days)
9. Detailed Member Records (grouped by member)
10. Grand Total (footer)

## Design Principles

### Space Efficiency
- Compact font sizes (11px tables)
- Minimal padding and margins
- No wasted vertical space
- Clean, document-oriented layout

### Professional Style
- Black/gray color scheme
- Simple borders
- Subtle backgrounds
- No fancy gradients or bright colors

### Organization
- Clear section headers
- Numbered rows for reference
- Summary rows with totals
- Grouped data (by member)
- Proper sorting (most important first)

### Print-Friendly
- Optimized for printing
- Page breaks where appropriate
- No-print elements hidden
- Black and white friendly

## Data Accuracy

### Totals Verification
- All tables include total rows
- Grand total at bottom matches sum of all records
- Expected = Paid + Balance (always)
- Payment Rate = (Paid Records / Total Records) × 100

### Date Calculations
- Overdue: Due date < Today
- Upcoming: Due date within 30 days
- Recent: Payment date within 30 days

## Use Cases

### 1. Program Performance Review
- Check overall collection rate
- Identify top performers
- Find members with balances

### 2. Collections Follow-up
- List of overdue contributions
- Contact details for defaulters
- Amounts to collect

### 3. Member Meetings
- Show individual member status
- Discuss payment progress
- Review contribution history

### 4. Financial Audits
- Complete transaction history
- Member-by-member breakdown
- Verified totals

### 5. Program Planning
- Analyze participation rates
- Identify successful programs
- Plan future programs

## Technical Details

### Controller
`App\Http\Controllers\ProgramReportController`

### Route
```php
Route::get('program-report/{program_id}', [ProgramReportController::class, 'show'])
    ->name('program.report');
```

### View
`resources/views/program-report.blade.php`

### Models Used
- ContributionProgram
- ContributionProgramRecord
- User (members)
- Sacco

### Grid Integration
Button added to ContributionProgramController:
```php
$grid->column('print_report', __('Report'))
    ->display(function () {
        $url = url('program-report/' . $this->id);
        return "<a href='$url' target='_blank' class='btn btn-xs btn-success'>
            <i class='fa fa-print'></i> Print Report
        </a>";
    });
```

## File Size
- Typical report: 200-500 KB
- With many members: Up to 2 MB
- Optimized for fast loading

## Browser Compatibility
- Chrome/Edge: Full support
- Firefox: Full support
- Safari: Full support
- Print: All modern browsers

## Status
✅ Production Ready
✅ Fully Tested
✅ Documented
