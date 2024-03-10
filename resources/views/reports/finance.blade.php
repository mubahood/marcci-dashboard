@php
    $isPrint = false;
    if (Str::contains($_SERVER['REQUEST_URI'], 'reports-finance-print')) {
        $isPrint = true;
    }
    $sacco = $r->sacco;
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


<p class="bg-danger">
    Lorem ipsum, dolor sit amet consectetur adipisicing elit. Deserunt tenetur et quisquam. Sint omnis nulla aliquam
    blanditiis aut facilis ipsa atque libero a, iusto ab, soluta quisquam, eligendi minus reprehenderit.
</p>
<table class="w-100">
    <tr>
        <td style="width: 16%">
            <img style="width: 100%; " src="{{ public_path('storage/' . $sacco->logo) }}">
        </td>
        <td>
            <div class="text-center">
                <p>
                    <img style="width: 35%; " src="{{ public_path('assets/bismillah.png') }}">
                </p>
                <p class="fs-26 text-center fw-700 mt-2 text-uppercase  " style="color: {{ $sacco->color }};">
                    {{ $sacco->name }}</p>
                <p><i>"{{ $sacco->motto }}"</i></p>
                <p class="fs-14 lh-6 mt-0">TEL: {{ $sacco->phone_number }},&nbsp;{{ $sacco->phone_number_2 }}</p>
                <p class="fs-14 lh-6 mt-0">EMAIL: {{ $sacco->email }}, WEBSITE: {{ $sacco->website }}</p>
                <p class="fs-14 mt-0">{{ $sacco->p_o_box }}, &nbsp; {{ $sacco->address }}</p>
            </div>
        </td>
        <td style="width: 16%">
        </td>
    </tr>
</table>

<hr style="height: 5px; background-color:  {{ $sacco->color }};" class=" my-2 mb-4">
<p class="fs-24 text-center fw-200 mt-2 text-uppercase black mb-4"><u>
        Termly Financial Report for the TERM {{ $r->name }}</u></p>
<p class="text-right mb-4"> <small><u>DATE: {{ $r->date }}</u></small></p>

{{-- 
<table style="width: 100%">
    <thead>
        <tr>
            <td style="width: 30%;">
                <div class="my-card mr-1">
                    <p class="black fs-14 fw-700">Expected Tution Fees</p>
                    <p class="py-3"><span>UGX</span><span
                            class="fs-26 fw-800">{{ number_format($r->total_expected_tuition) }}</span>
                    </p>
                    <p class="fw-400 fs-14 text-dark">Total sum of tution fees of term from all active students.</p>
                </div>
            </td>
            <td style="width: 30%;">
                <div class="my-card mx-1">
                    <p class="black fs-14 fw-700">Expected Services Fees</p>
                    <p class="py-3"><span>UGX</span><span
                            class="fs-26 fw-800">{{ number_format($r->total_expected_service_fees) }}</span></p>
                    <p class="fw-400 fs-14 text-dark">
                        Total sum of service subscription fees of this term</p>
                </div>
            </td>
            <td style="width: 30%;">
                <div class="my-card ml-1">
                    <p class="black fs-14 fw-700">Total Expected Income</p>
                    <p class="py-3"><span>UGX</span><span
                            class="fs-26 fw-800">{{ number_format($r->total_expected_service_fees + $r->total_expected_tuition) }}</span>
                    </p>
                    <p class="fw-400 fs-14 text-dark">Sum of tution fees and services subscriptions fees.</p>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 30%;" class="pt-2">
                <div class="my-card mr-1">
                    <p class="black fs-14 fw-700">Total Income</p>
                    <p class="py-3"><span>UGX</span><span
                            class="fs-26 fw-800">{{ number_format($r->total_payment_total) }}</span>
                    </p>
                    <p class="fw-400 fs-14 text-dark">SCHOOLPAY: {{ number_format($r->total_payment_school_pay) }},
                        CASH: {{ number_format($r->total_payment_manual_pay + $r->total_payment_mobile_app) }}</p>
                </div>
            </td>
            <td style="width: 30%;">
                <div class="my-card ml-1">
                    <p class="black fs-14 fw-700">Total Bursaries Offered</p>
                    <p class="py-3"><span>UGX</span><span
                            class="fs-26 fw-800">{{ number_format($r->total_bursaries_funds) }}</span></p>
                    <p class="fw-400 fs-14 text-dark">Total Sum of bursary funds offered this term.</p>
                </div>
            </td>
            <td style="width: 30%;">
                <div class="my-card mx-1">
                    <p class="black fs-14 fw-700">School Fees Balance</p>
                    <p class="py-3"><span>UGX</span><span
                            class="fs-26 fw-800">{{ number_format($r->total_school_fees_balance) }}</span></p>
                    <p class="fw-400 fs-14 text-dark">Total school fees balance of all active students.</p>
                </div>
            </td>
        </tr>

        <tr>
            <td style="width: 30%;" class="pt-2">
                <div class="my-card mr-1">
                    <p class="black fs-14 fw-700">Total Budget</p>
                    <p class="py-3"><span>UGX</span><span
                            class="fs-26 fw-800">{{ number_format($r->total_budget) }}</span>
                    </p>
                    <p class="fw-400 fs-14 text-dark">Total amount of money planned to be spent this term.</p>
                </div>
            </td>
            <td style="width: 30%;">
                <div class="my-card ml-1">
                    <p class="black fs-14 fw-700">Total Expenditure</p>
                    <p class="py-3"><span>UGX</span><span
                            class="fs-26 fw-800">{{ number_format($r->total_expense) }}</span></p>
                    <p class="fw-400 fs-14 text-dark">Total amount of money spent this term.</p>
                </div>
            </td>
            <td style="width: 30%;">
                <div class="my-card mx-1">
                    <p class="black fs-14 fw-700">Stock Value</p>
                    <p class="py-3"><span>UGX</span><span
                            class="fs-26 fw-800">{{ number_format($r->total_stock_value) }}</span></p>
                    <p class="fw-400 fs-14 text-dark">Current total stock value in stores.</p>
                </div>
            </td>
        </tr>
    </thead>
</table>

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
