@php
    use App\Models\Utils;
    $isPrint = false;
    if (Str::contains($_SERVER['REQUEST_URI'], 'reports-finance-print')) {
        $isPrint = true;
    }
    $sacco = $r->sacco;
    $isPrint = true;
@endphp
@if ($isPrint)
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <link rel="stylesheet" href="{{ public_path('/assets/styles.css') }}">
        <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">
    </head>

    <body>
@endif

<table class="w-100">
    <tr>
        <td style="width: 16%">
        </td>
        <td>
            <div class="text-center">
                <p class="fs-18 text-center fw-700 mt-2 text-uppercase  " style="color: black;">
                    {{ $sacco->name }}</p>
                <p class="fs-14 lh-6 mt-1">TEL: {{ $sacco->phone_number }},&nbsp;{{ $sacco->chairperson_phone_number }}
                </p>
                <p class="fs-14 lh-6 mt-1">EMAIL: {{ $sacco->email_address }}</p>
                <p class="fs-14 mt-1">{{ $sacco->physical_address }}</p>
            </div>
        </td>
        <td style="width: 16%">
            <img style="width: 80%; " src="{{ public_path('storage/' . $sacco->logo) }}">
        </td>
    </tr>
</table>

<hr style="height: 3px; background-color:  black;" class=" mt-3 mb-0">
<hr style="height: .3px; background-color:  black;" class=" mt-1 mb-4">
<p class="fs-18 text-center mt-2 text-uppercase black mb-4 fw-700"><u>
        {{ $r->title }}</u></p>
<p class="text-right mb-4"> <small><u>DATE: {{ Utils::my_date($r->created_at) }}</u></small></p>


<table style="width: 100%">
    <thead>
        <tr>
            <td style="width: 30%;">
                <div class="my-card mr-1">
                    <p class="black fs-14 fw-700">Balance</p>
                    <p class="py-3"><span>UGX</span><span
                            class="fs-26 fw-800">{{ number_format($r->balance) }}</span>
                    </p>
                    <p class="fw-400 fs-14 text-dark">{{ $r->Balance_DESCRIPTION }}</p>
                </div>
            </td>
            <td style="width: 30%;">
                <div class="my-card mx-1">
                    <p class="black fs-14 fw-700">Profits</p>
                    <p class="py-3"><span>UGX</span><span
                            class="fs-26 fw-800">{{ number_format($r->CYCLE_PROFIT) }}</span></p>
                    <p class="fw-400 fs-14 text-dark">{{ $r->CYCLE_PROFIT_DESCRIPTION }}</p>
                </div>
            </td>
            <td style="width: 30%;">
                <div class="my-card ml-1">
                    <p class="black fs-14 fw-700">Total Savings</p>
                    <p class="py-3"><span>UGX</span><span
                            class="fs-26 fw-800">{{ number_format($r->TOTAL_SAVING) }}</span>
                    </p>
                    <p class="fw-400 fs-14 text-dark">{{ $r->TOTAL_SAVING_DESCRIPTION }}</p>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 30%;" class="pt-2">
                <div class="my-card mr-1">
                    <p class="black fs-14 fw-700">Total Withdraw</p>
                    <p class="py-3"><span>UGX</span><span
                            class="fs-26 fw-800">{{ number_format($r->WITHDRAWAL) }}</span>
                    </p>
                    <p class="fw-400 fs-14 text-dark">{{ $r->WITHDRAWAL_DESCRIPTION }}</p>
                </div>
            </td>
            <td style="width: 30%;">
                <div class="my-card ml-1">
                    <p class="black fs-14 fw-700">Shares</p>
                    <p class="py-3"><span>UGX</span><span class="fs-26 fw-800">{{ number_format($r->SHARE) }}</span>
                    </p>
                    <p class="fw-400 fs-14 text-dark">{{ $r->SHARE_DESCRIPTION }}.
                        ({{ number_format($r->SHARE_COUNT) }} Shares)</p>
                </div>
            </td>
            <td style="width: 30%;">
                <div class="my-card mx-1">
                    <p class="black fs-14 fw-700">Total Loan</p>
                    <p class="py-3"><span>UGX</span><span
                            class="fs-26 fw-800">{{ number_format($r->LOAN_TOTAL_AMOUNT) }}</span></p>
                    <p class="fw-400 fs-14 text-dark">{{ $r->LOAN_TOTAL_AMOUNT_DESCRIPTION }}.
                        ({{ number_format($r->LOAN_COUNT) }} Loans).</p>
                </div>
            </td>
        </tr>

        <tr>
            <td style="width: 30%;" class="pt-2">
                <div class="my-card mr-1">
                    <p class="black fs-14 fw-700">Loan Payments</p>
                    <p class="py-3"><span>UGX</span><span
                            class="fs-26 fw-800">{{ number_format($r->LOAN_REPAYMENT) }}</span>
                    </p>
                    <p class="fw-400 fs-14 text-dark">{{ $r->LOAN_REPAYMENT_DESCRIPTION }}</p>
                </div>
            </td>
            <td style="width: 30%;">
                <div class="my-card ml-1">
                    <p class="black fs-14 fw-700">Unpaid Loan Balance</p>
                    <p class="py-3"><span>UGX</span><span
                            class="fs-26 fw-800">{{ number_format($r->LOAN_BALANCE) }}</span></p>
                    <p class="fw-400 fs-14 text-dark">{{ $r->LOAN_BALANCE_DESCRIPTION }}</p>
                </div>
            </td>
            <td style="width: 30%;">
                <div class="my-card mx-1">
                    <p class="black fs-14 fw-700">Loan Interest</p>
                    <p class="py-3"><span>UGX</span><span
                            class="fs-26 fw-800">{{ number_format($r->LOAN_INTEREST) }}</span></p>
                    <p class="fw-400 fs-14 text-dark">{{ $r->LOAN_INTEREST_DESCRIPTION }}</p>
                </div>
            </td>
        </tr>
    </thead>
</table>
{{-- 
<p class="black fs-18 fw-700 mt-3">Expected and Balance School Fees by Classes</p>
<hr class="black bg-dark my-1">
<table class="mt-2 w-100">
    <thead>
        <tr style=" border-bottom: 1px black solid;">
            <th class="text-left">Class</th>
            <th class="text-center">Active Students</th>
            <th class="text-right">Tution <small>(UGX)</small></th>
            <th class="text-right">Expected Fees <small>(UGX)</small></th>
            <th class="text-right">Balance <small>(UGX)</small></th>
        </tr>
    </thead>
    <tbody>
        @php
            $x = 0;
            $students_tot = 0;
            $expteced_tot = 0;
            $balance_tot = 0;
        @endphp
        @foreach ($r->classes as $item)
            @php
                $x++;
                $students_tot += count($item->verified_studentes);
                $expteced_tot += $item->total_bills;
                $balance_tot += $item->total_balance;
            @endphp
            <tr>
                <td class="text-left">{{ $x }}. &nbsp;{{ $item->name }}</td>
                <td class="text-center">{{ number_format(count($item->verified_studentes)) }}</td>
                <td class="text-right">{{ number_format($item->individual_fees) }}</td>
                <td class="text-right">{{ number_format($item->total_bills) }}</td>
                <td class="text-right">{{ number_format($item->total_balance) }}</td>
            </tr>
        @endforeach
        <tr style="border-top: 1px black solid; border-bottom: 1px black solid;">
            <th>TOTAL</th>
            <th class="text-center">{{ number_format($students_tot) }}</th>
            <th class="text-right"></th>
            <th class="text-right">{{ number_format($expteced_tot) }}</th>
            <th class="text-right">{{ number_format($balance_tot) }}</th>
        </tr>
    </tbody>
</table>

<p class="black fs-18 fw-700 mt-3">Services and Service Subscriptions Summary</p>
<hr class="black bg-dark my-1">
<table class="mt-2 w-100">
    <thead>
        <tr style=" border-bottom: 1px black solid;">
            <th class="text-left">Service</th>
            <th class="text-right">Total Amount <small>(UGX)</small></th>
        </tr>
    </thead>
    <tbody>
        @php
            $x = 0;
            $total = 0;
        @endphp
        @foreach ($r->services as $item)
            @php
                $x++;
                $total += $item->subscriptions_total;
            @endphp
            <tr>
                <td class="text-left">{{ $x }}. &nbsp;{{ $item->name }}</td>

                <td class="text-right">{{ number_format($item->subscriptions_total) }}</td>
            </tr>
        @endforeach
        <tr style="border-top: 1px black solid; border-bottom: 1px black solid;">
            <th>TOTAL</th>
            <th class="text-right">{{ number_format($total) }}</th>
        </tr>
    </tbody>
</table>

<p class="black fs-18 fw-700 mt-3">Services and Service Subscriptions Details</p>
<hr class="black bg-dark my-1">
<table class="mt-2 w-100">
    <thead>
        <tr style=" border-bottom: 1px black solid;">
            <th class="text-left">Service</th>
            <th class="text-center">Total Subscribers</th>
            <th class="text-right">Service Fee <small>(UGX)</small></th>
            <th class="text-right">Total Amount <small>(UGX)</small></th>
        </tr>
    </thead>
    <tbody>
        @php
            $x = 0;
            $total = 0;
        @endphp
        @foreach ($r->services_sub_category as $item)
            @php
                $x++;
                $total += $item->subscriptions_total;
            @endphp
            <tr>
                <td class="text-left">{{ $x }}. &nbsp;{{ $item->name }}</td>
                <td class="text-center">{{ number_format(count($item->subsList)) }}</td>
                <td class="text-right">{{ number_format($item->fee) }}</td>
                <td class="text-right">{{ number_format($item->subscriptions_total) }}</td>
            </tr>
        @endforeach
        <tr style="border-top: 1px black solid; border-bottom: 1px black solid;">
            <th>TOTAL</th>
            <th class="text-center"></th>
            <th class="text-right"></th>
            <th class="text-right">{{ number_format($total) }}</th>
        </tr>
    </tbody>
</table>


<p class="black fs-18 fw-700 mt-3">Bursary Schemes and Bursary Benefiaries</p>
<hr class="black bg-dark my-1">
<table class="mt-2 w-100">
    <thead>
        <tr style=" border-bottom: 1px black solid;">
            <th class="text-left">Bursary Schemes</th>
            <th class="text-center">Total Benefiaries</th>
            <th class="text-right">Bursary Fund<small>(UGX)</small></th>
            <th class="text-right">Total Amount<small>(UGX)</small></th>
        </tr>
    </thead>
    <tbody>
        @php
            $x = 0;
            $total = 0;
        @endphp
        @foreach ($r->bursaries as $item)
            @php
                $x++;
                $total += $item->total_fund;
            @endphp
            <tr>
                <td class="text-left">{{ $x }}. &nbsp;{{ $item->name }}</td>
                <td class="text-center">{{ number_format($item->active_benefiaries) }}</td>
                <td class="text-right">{{ number_format($item->fund) }}</td>
                <td class="text-right">{{ number_format($item->total_fund) }}</td>
            </tr>
        @endforeach
        <tr style="border-top: 1px black solid; border-bottom: 1px black solid;">
            <th>TOTAL</th>
            <th class="text-center"></th>
            <th class="text-right"></th>
            <th class="text-right">{{ number_format($total) }}</th>
        </tr>
    </tbody>
</table>

 --}}


@if ($isPrint)
    </body>

    </html>
@endif
