<?php

namespace App\Http\Controllers;

use App\Models\Association;
use App\Models\Contribution;
use App\Models\CounsellingCentre;
use App\Models\Crop;
use App\Models\CropProtocol;
use App\Models\Cycle;
use App\Models\Event;
use App\Models\Garden;
use App\Models\GardenActivity;
use App\Models\Group;
use App\Models\Institution;
use App\Models\Job;
use App\Models\Loan;
use App\Models\LoanRequest;
use App\Models\LoanScheem;
use App\Models\LoanTransaction;
use App\Models\LogError;
use App\Models\NewsPost;
use App\Models\Person;
use App\Models\Product;
use App\Models\Sacco;
use App\Models\ServiceProvider;
use App\Models\ShareRecord;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ApiResurceController extends Controller
{

    use ApiResponser;

    public function loan_schemes(Request $r)
    {
        $u = auth('api')->user();

        if ($u == null) {
            return $this->error('User not found.');
        }

        return $this->success(
            LoanScheem::where(
                [
                    'sacco_id' => $u->sacco_id
                ]
            )->orderby('id', 'desc')->get(),
            $message = "Success.",
            200
        );
    }

    public function loan_transactions(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $conds = [];
        if ($u->isRole('sacco')) {
            $conds = [
                'sacco_id' => $u->sacco_id
            ];
        } else {
            $conds = [
                'user_id' => $u->id
            ];
        }
        return $this->success(
            LoanTransaction::where($conds)->orderby('id', 'desc')->get(),
            $message = "Success",
            200
        );
    }

    public function manifest(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $U = User::find($u->id);
        $U->updated_at = Carbon::now();
        $U->save();
        $sacco = Sacco::find($u->sacco_id);
        $members = User::where(['sacco_id' => $sacco->id])->get();
        $sacco->member_count = count($members);
        $sacco->members_alive = 0;
        $sacco->members_contribution = 0;
        $sacco->members_optional = 0;
        $sacco->members_na = 0;
        $sacco->members_male = 0;
        $sacco->members_female = 0;
        $sacco->members_educ_none = 0;
        $sacco->members_educ_Primary = 0;
        $sacco->members_educ_Secondary = 0;
        $sacco->members_educ_A_Level = 0;
        $sacco->members_educ_Certificate = 0;
        $sacco->members_educ_Diploma = 0;
        $sacco->members_educ_Bachelor = 0;
        $sacco->members_educ_Masters = 0;
        $sacco->members_educ_PhD = 0;

        $sacco->members_age_0_18 = 0;
        $sacco->members_age_19_25 = 0;
        $sacco->members_age_26_30 = 0;
        $sacco->members_age_31_45 = 0;
        $sacco->members_age_46_75 = 0;
        $sacco->members_age_76 = 0;

        $now = Carbon::now();
        foreach ($members as $key => $member) {
            if ($member->reg_number == 'Alive') {
                $sacco->members_alive = $sacco->members_alive + 1;

                if (strlen($member->dob) > 3) {
                    try {
                        $dob = Carbon::parse($member->dob);
                        $diff = $dob->diffInYears($now);
                        $diff = abs($diff);

                        if ($diff <= 18) {
                            $sacco->members_age_0_18++;
                        } elseif ($diff <= 25) {
                            $sacco->members_age_19_25++;
                        } elseif ($diff <= 30) {
                            $sacco->members_age_26_30++;
                        } elseif ($diff <= 45) {
                            $sacco->members_age_26_30++;
                        } elseif ($diff <= 75) {
                            $sacco->members_age_46_75++;
                        } elseif ($diff > 75) {
                            $sacco->members_age_76++;
                        }
                    } catch (\Throwable $th) {
                    }
                }

                if ($member->other_link == 'None') {
                    $sacco->members_educ_none++;
                } else if ($member->other_link == 'Primary') {
                    $sacco->members_educ_Primary++;
                } else if ($member->other_link == 'Secondary') {
                    $sacco->members_educ_Secondary++;
                } else if ($member->other_link == 'A-Level') {
                    $sacco->members_educ_A_Level++;
                } else if ($member->other_link == 'Certificate') {
                    $sacco->members_educ_Certificate++;
                } else if ($member->other_link == 'Diploma') {
                    $sacco->members_educ_Diploma++;
                } else if ($member->other_link == 'Bachelor') {
                    $sacco->members_educ_Bachelor++;
                } else if ($member->other_link == 'Masters') {
                    $sacco->members_educ_Masters++;
                } else if ($member->other_link == 'PhD') {
                    $sacco->members_educ_PhD++;
                }

                if ($member->sex == 'Male') {
                    $sacco->members_male++;
                } else {
                    $sacco->members_female++;
                }

                if ($member->language == 'Compulsory') {
                    $sacco->members_contribution++;
                } else if ($member->language == 'Optional') {
                    $sacco->members_optional++;
                } else {
                    $sacco->members_na++;
                }
            }
        }


        // //set header to json output
        // header('Content-Type: application/json');
        // echo json_encode($sacco);
        // die();

        return $this->success(
            json_encode([
                'balance' => $u->balance,
                'name' => $u->name,
                'id' => $u->id,
                'updated_at' => $u->updated_at,
                'sacco' => $sacco,
            ]),
            $message = "Success",
            200
        );
    }


    public function share_record_create(Request $r)
    {
        $admin = auth('api')->user();
        if ($admin == null) {
            return $this->error('User not found.');
        }
        if ($r->user_id == null) {
            return $this->error('User not found.');
        }
        //check for number_of_shares
        if ($r->number_of_shares == null) {
            return $this->error('Number of shares not found.');
        }
        $u = User::find($r->user_id);
        if ($u == null) {
            return $this->error('User not found.');
        }
        $sacco = Sacco::find($u->sacco_id);
        if ($sacco == null) {
            return $this->error('Sacco not found.');
        }
        $share_record = new ShareRecord();
        $share_record->user_id = $u->id;
        $share_record->number_of_shares = $r->number_of_shares;
        $share_record->created_by_id = $admin->id;

        try {
            $share_record->save();
        } catch (\Throwable $th) {
            return $this->error('Failed to save share record, because ' . $th->getMessage() . '');
        }
        return $this->success(
            $share_record,
            $message = "Success",
            200
        );
    }


    public function request_otp_sms(Request $r)
    {

        $r->validate([
            'phone_number' => 'required',
        ]);


        $phone_number = $r->phone_number;
        $isEmail = false;

        //check if $phone_number is email address
        if (filter_var($phone_number, FILTER_VALIDATE_EMAIL)) {
            $isEmail = true;
        }

        if (!$isEmail) {
            $phone_number = Utils::prepare_phone_number($r->phone_number);
            if (!Utils::phone_number_is_valid($phone_number)) {
                return $this->error('Invalid phone number.');
            }
            $acc = User::where(['phone_number' => $phone_number])->first();
            if ($acc == null) {
                $acc = User::where(['username' => $phone_number])->first();
            }
        } else {
            $acc = User::where(['email' => $phone_number])->first();
            if ($acc == null) {
                $acc = User::where(['username' => $phone_number])->first();
            }
        }

        if ($acc == null) {
            return $this->error('Account not found.');
        }
        $otp = rand(10000, 99999) . "";

        if (
            str_contains($phone_number, '256783204665') ||
            str_contains(strtolower($acc->first_name), 'test') ||
            str_contains(strtolower($acc->last_name), 'test')
        ) {
            $otp = '12345';
        }
        $resp = null;

        $msg = '';
        if (!$isEmail) {
            try {
                $resp = Utils::send_sms($phone_number, $otp . ' is your MobiSave OTP.');
                $msg = 'OTP sent to ' . $phone_number . '';
            } catch (Exception $e) {
                LogError::create([
                    'message' => $e->getMessage() . '',
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'trace' => $e->getTraceAsString(),
                    'url' => $r->url(),
                    'method' => 'send_sms',
                    'input' => json_encode([
                        'GET' => $_GET,
                        'POST' => $_POST,
                    ]),
                    'user_agent' => $r->header('User-Agent'),
                    'ip' => $r->ip(),
                ]);
                return $this->error('Failed to send OTP  because ' . $e->getMessage() . '');
            }
        } else {
            try {
                Utils::mail_sender([
                    'email' => $phone_number,
                    'name' => $acc->name,
                    'subject' => env('APP_NAME') . ' OTP - ' . date('Y-m-d H:i:s'),
                    'body' => $otp . ' is your  ' . env('APP_NAME') . ' OTP.'
                ]);
                $msg = 'OTP sent to ' . $phone_number . '';
            } catch (Exception $e) {
                LogError::create([
                    'message' => $e->getMessage() . '',
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'trace' => $e->getTraceAsString(),
                    'url' => $r->url(),
                    'method' => 'mail_sender',
                    'input' => json_encode([
                        'GET' => $_GET,
                        'POST' => $_POST,
                    ]),
                    'user_agent' => $r->header('User-Agent'),
                    'ip' => $r->ip(),
                ]);
            }
        }

        $acc->intro = $otp;
        $acc->save();
        return $this->success(
            $otp . "",
            $message = $msg,
            1
        );
    }


    public function loans(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $conds = [];
        if ($u->isRole('sacco')) {
            $conds = [
                'sacco_id' => $u->sacco_id
            ];
        } else {
            $conds = [
                'user_id' => $u->id
            ];
        }
        return $this->success(
            Loan::where($conds)->orderby('id', 'desc')->get(),
            $message = "Success",
            200
        );
    }

    public function cycles(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $conds = [];
        $conds = [
            'sacco_id' => $u->sacco_id
        ];
        return $this->success(
            Cycle::where($conds)->orderby('id', 'desc')->get(),
            $message = "Success",
            200
        );
    }

    public function share_records(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $conds = [];

        if ($u->isRole('sacco')) {
            $conds = [
                'sacco_id' => $u->sacco_id
            ];
        } else {
            $conds = [
                'user_id' => $u->id
            ];
        }

        return $this->success(
            ShareRecord::where($conds)->orderby('id', 'desc')->get(),
            $message = "Success",
            200
        );
    }


    public function transactions(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $conds = [];
        if ($u->isRole('sacco')) {
            $conds = [
                'sacco_id' => $u->sacco_id
            ];
        } else {
            $conds = [
                'user_id' => $u->id
            ];
        }
        return $this->success(
            Transaction::where($conds)->orderby('id', 'desc')->get(),
            $message = "Success",
            200
        );
    }


    public function saccos(Request $r)
    {
        return $this->success(
            Sacco::where([])->orderby('id', 'desc')->get(),
            $message = "Sussess",
            200
        );
    }

    public function loan_create(Request $r)
    {

        $admin = auth('api')->user();
        if ($admin == null) {
            return $this->error('Admin not found.');
        }

        if (!password_verify($r->password, $admin->password)) {
            return $this->error('Invalid password.');
        }

        if (!isset($r->user_id)) {
            return $this->error('User account id not found.');
        }
        $u = User::find($r->user_id);

        if ($u == null) {
            return $this->error('User account found.');
        }

        if (
            $r->loan_scheem_id == null ||
            $r->amount == null
        ) {
            return $this->error('Some Information is still missing. Fill the missing information and try again.');
        }

        $loan_scheem = LoanScheem::find($r->loan_scheem_id);
        if ($loan_scheem == null) {
            return $this->error('Loan scheem not found.');
        }

        $total_deposit = Transaction::where([
            'user_id' => $u->id,
        ])
            ->where('amount', '>', 0)
            ->sum('amount');


        if ($loan_scheem->min_balance > $total_deposit) {
            return $this->error('You have not saved enough money to apply for this loan. You need to save at least UGX ' . number_format($loan_scheem->min_balance) . ' to apply for this loan.');
        }

        $oldLoans = Loan::where([
            'user_id' => $u->id,
            'is_fully_paid' => 'No',
        ])->get();

        if (count($oldLoans) > 0) {
            return $this->error('You have an existing loan that is not fully paid. You cannot apply for another loan until you have fully paid the existing loan.');
        }

        $sacco = Sacco::find($u->sacco_id);
        if ($sacco == null) {
            return $this->error('Sacco not found.');
        }

        if ($loan_scheem->max_amount < $r->amount) {
            return $this->error('You cannot apply for a loan of more than UGX ' . number_format($loan_scheem->max_amount) . '.');
        }

        if ($sacco->balance < $r->amount) {
            return $this->error('The sacco does not have enough money to lend you UGX ' . number_format($r->amount) . '.');
        }



        $amount = $r->amount;
        $amount = abs($amount);
        $amount = -1 * $amount;

        $request  = new LoanRequest();
        $request->sacco_id = $u->sacco_id;
        $request->applicant_id = $u->id;
        $request->approved_by_id = $u->id;
        $request->loan_scheem_id = $r->loan_scheem_id;
        $request->cycle_id = 1;
        $request->amount = $amount;
        $request->reason = $r->reason;
        $request->status = 'Pending';
        $request->comment = '';
        try {
            $request->save();
        } catch (\Throwable $th) {
            return $this->error('Failed to save loan request, because ' . $th->getMessage() . '');
        }

        return $this->success(
            $request,
            $message = "Loan request sent successfully. You will receive a confirmation message shortly.",
            200
        );

        DB::beginTransaction();
        try {
            $loan = new Loan();
            $loan->sacco_id = $u->sacco_id;
            $loan->user_id = $u->id;
            $loan->loan_scheem_id = $r->loan_scheem_id;
            $loan->amount = $amount;
            $loan->balance = $amount;
            $loan->is_fully_paid = 'No';
            $loan->scheme_name = $loan_scheem->name;
            $loan->scheme_description = $loan_scheem->description;
            $loan->scheme_initial_interest_type = $loan_scheem->initial_interest_type;
            $loan->scheme_initial_interest_flat_amount = $loan_scheem->initial_interest_flat_amount;
            $loan->scheme_initial_interest_percentage = $loan_scheem->initial_interest_percentage;
            $loan->scheme_bill_periodically = $loan_scheem->bill_periodically;
            $loan->scheme_billing_period = $loan_scheem->billing_period;
            $loan->scheme_periodic_interest_type = $loan_scheem->periodic_interest_type;
            $loan->scheme_periodic_interest_percentage = $loan_scheem->periodic_interest_percentage;
            $loan->scheme_periodic_interest_flat_amount = $loan_scheem->periodic_interest_flat_amount;
            $loan->scheme_min_amount = $loan_scheem->min_amount;
            $loan->scheme_max_amount = $loan_scheem->max_amount;
            $loan->scheme_min_balance = $loan_scheem->min_balance;
            $loan->scheme_max_balance = $loan_scheem->max_balance;
            $loan->reason = $r->reason;
            try {
                $loan->save();
                //success
            } catch (\Throwable $th) {
                DB::rollBack();
                return $this->error('Failed to save loan, because ' . $th->getMessage() . '');
            }
            DB::commit();
            return $this->success(null, $message = "Loan applied successfully. You will receive a confirmation message shortly.", 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->error('Failed, because ' . $th->getMessage() . '');
        }
    }

    public function transactions_create(Request $r)
    {
        $admin = auth('api')->user();
        if ($admin == null) {
            return $this->error('User not found.');
        }
        /*         if ($admin->user_type != 'Admin') {
            return $this->error('Only admins can create a transaction.');
        } */

        $u = User::find($r->user_id);
        if ($u == null) {
            return $this->error('Receiver account not found.');
        }

        if ($u == null) {
            return $this->error('User not found.');
        }
        if (
            $r->type == null ||
            $r->amount == null
        ) {
            return $this->error('Some Information is still missing. Fill the missing information and try again.');
        }

        include_once(app_path() . '/Models/Utils.php');

        if (!in_array($r->type, TRANSACTION_TYPES)) {
            throw new Exception("Invalid transaction type.");
        }

        if ($r->type == 'CONTRIBUTION') {

            $contribution = Contribution::where([
                'id' => $r->loan_id,
            ])->first();

            if ($contribution == null) {
                return $this->error('Contribution not found.');
            }
            if (
                $r->payment_type != 'CASH' &&
                $r->payment_type != 'ACCOUNT'
            ) {
                return $this->error('Payment type not found.');
            }
            $amount = $r->amount;
            $amount = abs($amount);

            if ($r->payment_type == 'CASH') {

                try {
                    DB::beginTransaction();
                    //create positive transaction for user
                    $transaction_user = new Transaction();
                    $transaction_user->user_id = $u->id;
                    $transaction_user->source_user_id = $admin->id;
                    $transaction_user->sacco_id = $u->sacco_id;
                    $transaction_user->type = 'SAVING';
                    $transaction_user->source_type = 'SAVING';
                    $transaction_user->amount = $amount;
                    $transaction_user->details = $r->description;
                    $transaction_user->description =  "Saving of UGX " . number_format($amount) . " from {$u->phone_number} - $u->name.";
                    try {
                        $transaction_user->save();
                    } catch (\Throwable $th) {
                        DB::rollback();
                        return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
                    }

                    //add balance to sacc account
                    $transaction_sacco = new Transaction();
                    $transaction_sacco->user_id = $admin->id;
                    $transaction_sacco->source_user_id = $u->id;
                    $transaction_sacco->sacco_id = $u->sacco_id;
                    $transaction_sacco->type = 'SAVING';
                    $transaction_sacco->source_type = 'SAVING';
                    $transaction_sacco->amount = $amount;
                    $transaction_user->details = $r->description;
                    $transaction_sacco->description = "Saving of UGX " . number_format($amount) . " from {$u->phone_number} - $u->name.";
                    try {
                        $transaction_sacco->save();
                    } catch (\Throwable $th) {
                        DB::rollback();
                        return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
                    }
                    try {
                        $transaction_sacco->save();
                    } catch (\Throwable $th) {
                        DB::rollback();
                        return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    // something went wrong
                    return $this->error('Failed to save transaction, because ' . $e->getMessage() . '');
                }
            } else {
                $amount = abs($r->amount);
                if ($u->balance < $amount) {
                    return $this->error('You do not have enough money to withdraw UGX ' . number_format($amount) . '. Your balance is UGX ' . number_format($u->balance) . '.');
                }
            }



            //transaction from 
            try {
                DB::beginTransaction();
                //create negative transaction for user
                $transaction_user = new Transaction();
                $transaction_user->user_id = $u->id;
                $transaction_user->source_user_id = $admin->id;
                $transaction_user->sacco_id = $u->sacco_id;
                $transaction_user->source_bank_transaction_id = $contribution->id;
                $transaction_user->type = 'CONTRIBUTION';
                $transaction_user->source_type = 'CONTRIBUTION';
                $transaction_user->amount = (-1 * abs($amount));
                $transaction_user->details = $r->description;
                $transaction_user->description =  "Contribution of UGX " . number_format($amount) . " due to {$contribution->name} - {$contribution->id} from {$u->phone_number} - $u->name.";
                try {
                    $transaction_user->save();
                } catch (\Throwable $th) {
                    DB::rollback();
                    return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
                }

                //add balance to sacc account
                $transaction_sacco = new Transaction();
                $transaction_sacco->user_id = $admin->id;
                $transaction_sacco->source_user_id = $u->id;
                $transaction_sacco->sacco_id = $u->sacco_id;
                $transaction_sacco->type = 'CONTRIBUTION';
                $transaction_sacco->source_type = 'CONTRIBUTION';
                $transaction_sacco->source_bank_transaction_id = $contribution->id;
                $transaction_sacco->amount = abs($amount);
                $transaction_sacco->details = $r->description;
                $transaction_sacco->description =  "Contribution of UGX " . number_format($amount) . " due to {$contribution->name} - {$contribution->id} from {$u->phone_number} - $u->name.";
                try {
                    $transaction_sacco->save();
                } catch (\Throwable $th) {
                    DB::rollback();
                    return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
                }
                try {
                    $transaction_sacco->save();
                } catch (\Throwable $th) {
                    DB::rollback();
                    return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
                }

                DB::commit();
                return $this->success(null, $message = "Contribution of UGX " . number_format($amount) . " was successful. Your balance is now UGX " . number_format($u->balance) . ".", 200);
            } catch (\Exception $e) {
                DB::rollback();
                // something went wrong
                return $this->error('Failed to save transaction, because ' . $e->getMessage() . '');
            }
        } else if ($r->type == 'WITHDRAWAL') {
            $amount = abs($r->amount);
            if ($u->balance < $amount) {
                return $this->error('You do not have enough money to withdraw UGX ' . number_format($amount) . '. Your balance is UGX ' . number_format($u->balance) . '.');
            }
            $amount = -1 * $amount;
            try {
                DB::beginTransaction();
                //create positive transaction for user
                $transaction_user = new Transaction();
                $transaction_user->user_id = $u->id;
                $transaction_user->source_user_id = $admin->id;
                $transaction_user->sacco_id = $u->sacco_id;
                $transaction_user->type = 'WITHDRAWAL';
                $transaction_user->source_type = 'WITHDRAWAL';
                $transaction_user->amount = $amount;
                $transaction_user->details = $r->description;
                $transaction_user->description = "Withdrawal of UGX " . number_format($amount) . " from {$u->phone_number} - $u->name.";
                try {
                    $transaction_user->save();
                } catch (\Throwable $th) {
                    DB::rollback();
                    return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
                }

                //add balance to sacc account
                $transaction_sacco = new Transaction();
                $transaction_sacco->user_id = $admin->id;
                $transaction_sacco->source_user_id = $u->id;
                $transaction_sacco->sacco_id = $u->sacco_id;
                $transaction_sacco->type = 'WITHDRAWAL';
                $transaction_sacco->source_type = 'WITHDRAWAL';
                $transaction_sacco->amount = $amount;
                $transaction_sacco->details = $r->description;
                $transaction_sacco->description = "Withdrawal of UGX " . number_format($amount) . " from {$u->phone_number} - $u->name.";
                try {
                    $transaction_sacco->save();
                } catch (\Throwable $th) {
                    DB::rollback();
                    return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
                }
                try {
                    $transaction_sacco->save();
                } catch (\Throwable $th) {
                    DB::rollback();
                    return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
                }

                DB::commit();
                return $this->success(null, $message = "Withdrawal of UGX " . number_format($amount) . " was successful. Your balance is now UGX " . number_format($u->balance) . ".", 200);
            } catch (\Exception $e) {
                DB::rollback();
                // something went wrong
                return $this->error('Failed to save transaction, because ' . $e->getMessage() . '');
            }
        } elseif ($r->type == 'FINE') {
            $amount = abs($r->amount);
            try {
                DB::beginTransaction();
                //create NEGATIVE transaction for user
                $transaction_user = new Transaction();
                $transaction_user->user_id = $u->id;
                $transaction_user->source_user_id = $admin->id;
                $transaction_user->sacco_id = $u->sacco_id;
                $transaction_user->type = 'FINE';
                $transaction_user->source_type = 'FINE';
                $transaction_user->amount = -1 * $amount;
                $transaction_user->details = $r->description;
                $transaction_user->description = "Fine of UGX " . number_format($amount) . " from {$u->phone_number} - $u->name. Reason: {$r->description}.";
                try {
                    $transaction_user->save();
                } catch (\Throwable $th) {
                    DB::rollback();
                    return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
                }

                //add balance to sacc account
                $transaction_sacco = new Transaction();
                $transaction_sacco->user_id = $admin->id;
                $transaction_sacco->source_user_id = $u->id;
                $transaction_sacco->sacco_id = $u->sacco_id;
                $transaction_sacco->type = 'FINE';
                $transaction_sacco->source_type = 'FINE';
                $transaction_sacco->amount = $amount;
                $transaction_sacco->details = $r->description;
                $transaction_sacco->description = "Fine of UGX " . number_format($amount) . " from {$u->phone_number} - $u->name. Reason: {$r->description}.";
                try {
                    $transaction_sacco->save();
                } catch (\Throwable $th) {
                    DB::rollback();
                    return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
                }
                try {
                    $transaction_sacco->save();
                } catch (\Throwable $th) {
                    DB::rollback();
                    return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
                }

                DB::commit();
                return $this->success(null, $message = "Fine of UGX " . number_format($amount) . " was successful. Your balance is now UGX " . number_format($u->balance) . ".", 200);
            } catch (\Exception $e) {
                DB::rollback();
                // something went wrong
                return $this->error('Failed to save transaction, because ' . $e->getMessage() . '');
            }
        } else if ($r->type == 'SAVING') {

            $amount = abs($r->amount);
            try {
                DB::beginTransaction();
                //create positive transaction for user
                $transaction_user = new Transaction();
                $transaction_user->user_id = $u->id;
                $transaction_user->source_user_id = $admin->id;
                $transaction_user->sacco_id = $u->sacco_id;
                $transaction_user->type = 'SAVING';
                $transaction_user->source_type = 'SAVING';
                $transaction_user->amount = $amount;
                $transaction_user->details = $r->description;
                $transaction_user->description =  "Saving of UGX " . number_format($amount) . " from {$u->phone_number} - $u->name.";
                try {
                    $transaction_user->save();
                } catch (\Throwable $th) {
                    DB::rollback();
                    return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
                }

                //add balance to sacc account
                $transaction_sacco = new Transaction();
                $transaction_sacco->user_id = $admin->id;
                $transaction_sacco->source_user_id = $u->id;
                $transaction_sacco->sacco_id = $u->sacco_id;
                $transaction_sacco->type = 'SAVING';
                $transaction_sacco->source_type = 'SAVING';
                $transaction_sacco->amount = $amount;
                $transaction_user->details = $r->description;
                $transaction_sacco->description = "Saving of UGX " . number_format($amount) . " from {$u->phone_number} - $u->name.";
                try {
                    $transaction_sacco->save();
                } catch (\Throwable $th) {
                    DB::rollback();
                    return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
                }
                try {
                    $transaction_sacco->save();
                } catch (\Throwable $th) {
                    DB::rollback();
                    return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
                }

                DB::commit();
                return $this->success(null, $message = "Fine of UGX " . number_format($amount) . " was successful. Your balance is now UGX " . number_format($u->balance) . ".", 200);
            } catch (\Exception $e) {
                DB::rollback();
                // something went wrong
                return $this->error('Failed to save transaction, because ' . $e->getMessage() . '');
            }


            //create positive transaction for sacco
        } else if ($r->type == 'LOAN_REPAYMENT') {
            $loan = Loan::find($r->loan_id);
            if ($loan == null) {
                return $this->error('Loan not found.');
            }
            $amount = abs($r->amount);
            if (((int)($amount)) > ((abs($loan->balance)))) {
                return $this->error('You cannot pay more than the loan balance.');
            }
            $record = new LoanTransaction();
            $record->user_id = $u->id;
            $acc_balance = $u->balance;

            if ($amount > $acc_balance) {
                return $this->error('You do not have enough money to pay this loan. Your balance is UGX ' . number_format($acc_balance) . '.');
            }

            $amount = abs($r->amount);
            try {
                DB::beginTransaction();
                //reduce user balance
                $transaction_user = new Transaction();
                $transaction_user->user_id = $u->id;
                $transaction_user->source_user_id = $admin->id;
                $transaction_user->sacco_id = $u->sacco_id;
                $transaction_user->type = 'LOAN_REPAYMENT';
                $transaction_user->source_type = 'LOAN_REPAYMENT';
                $transaction_user->amount = -1 * $amount;
                $transaction_user->description = "Loan Repayment of UGX " . number_format($amount) . " to {$u->phone_number} - $u->name. Loan Scheem: {$loan->scheme_name}. Reference: {$loan->id}.";
                $transaction_user->details = "Loan Repayment of UGX " . number_format($amount) . " to {$u->phone_number} - $u->name. Loan Scheem: {$loan->scheme_name}. Reference: {$loan->id}.";
                try {
                    $transaction_user->save();
                } catch (\Throwable $th) {
                    DB::rollback();
                    return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
                }

                //add balance to sacc account
                $transaction_sacco = new Transaction();
                $transaction_sacco->user_id = $admin->id;
                $transaction_sacco->source_user_id = $u->id;
                $transaction_sacco->sacco_id = $u->sacco_id;
                $transaction_sacco->type = 'LOAN_REPAYMENT';
                $transaction_sacco->source_type = 'LOAN_REPAYMENT';
                $transaction_sacco->amount = $amount;
                $transaction_sacco->description = "Loan Repayment of UGX " . number_format($amount) . " from {$u->phone_number} - $u->name. Loan Scheem: {$loan->scheme_name}. Reference: {$loan->id}.";
                $transaction_sacco->details = "Loan Repayment of UGX " . number_format($amount) . " from {$u->phone_number} - $u->name. Loan Scheem: {$loan->scheme_name}. Reference: {$loan->id}.";
                try {
                    $transaction_sacco->save();
                } catch (\Throwable $th) {
                    DB::rollback();
                    return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
                }

                //create loan transaction
                $loan_transaction = new LoanTransaction();
                $loan_transaction->user_id = $u->id;
                $loan_transaction->loan_id = $loan->id;
                $loan_transaction->sacco_id = $u->sacco_id;
                $loan_transaction->type = 'LOAN';
                $loan_transaction->amount = $amount;
                $loan_transaction->description = "Loan Repayment of UGX " . number_format($amount) . " from {$u->phone_number} - $u->name. Loan Scheem: {$loan->scheme_name}. Reference: {$loan->id}.";
                try {
                    $loan_transaction->save();
                } catch (\Throwable $th) {
                    DB::rollback();
                    return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
                }
                DB::commit();
                return $this->success(null, $message = "Loan repayment of UGX " . number_format($amount) . " was successful. Your balance is now UGX " . number_format($u->balance) . ".", 200);
            } catch (\Exception $e) {
                DB::rollback();
                // something went wrong
                return $this->error('Failed to save transaction, because ' . $e->getMessage() . '');
            }
            return;
        }


        $tra = new Transaction();
        $tra->user_id = $u->id;
        $tra->source_user_id = $admin->id;
        $tra->type = $r->type;
        $tra->source_type = $r->source_type;
        $tra->source_mobile_money_number = $r->source_mobile_money_number;
        $tra->source_mobile_money_transaction_id = $r->source_mobile_money_transaction_id;
        $tra->source_bank_account_number = $r->source_bank_account_number;
        $tra->source_bank_transaction_id = $r->source_bank_transaction_id;
        $tra->desination_type = $r->desination_type;
        $tra->desination_mobile_money_number = $r->desination_mobile_money_number;
        $tra->desination_mobile_money_transaction_id = $r->desination_mobile_money_transaction_id;
        $tra->desination_bank_account_number = $r->desination_bank_account_number;
        $tra->desination_bank_transaction_id = $r->desination_bank_transaction_id;
        $tra->amount = $r->amount;
        $tra->description = $r->description;
        $tra->details = $r->details;

        try {
            $tra->save();
            return $this->success(null, $message = "Transaction created successfully.", 200);
        } catch (\Throwable $th) {
            return $this->error('Failed to save transaction, because ' . $th->getMessage() . '');
        }
    }


    public function transactions_transfer(Request $r)
    {
        $sender = auth('api')->user();
        if ($sender == null) {
            return $this->error('User not found.');
        }
        if (
            $r->amount == null ||
            $r->desination_type == null ||
            $r->receiver_id == null
        ) {
            return $this->error('Some Information is still missing. Fill the missing information and try again.');
        }

        $receiver = User::find($r->receiver_id);
        if ($receiver == null) {
            return $this->error('Receiver not found.');
        }

        try {
            Transaction::send_money($sender->id, $receiver->id, $r->amount, $r->description, $r->desination_type);
            return $this->success(null, $message = "Sent UGX " . number_format($r->amount) . " to {$receiver->phone_number} - $receiver->name. Your balance is now UGX " . number_format($sender->balance) . ".", 200);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage() . '');
        }
    }

    public function sacco_join_request(Request $r)
    {

        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $sacco = Sacco::find($r->sacco_id);
        if ($sacco == null) {
            return $this->error('Sacco not found.');
        }
        $user = User::find($u->id);
        $user->sacco_join_status = 'Pending';
        $user->save();
        return $this->success(
            'Success',
            $message = "Request submitted successfully.",
        );
    }

    public function crops(Request $r)
    {
        $items = [];

        foreach (Crop::all() as $key => $crop) {
            $protocols = CropProtocol::where([
                'crop_id' => $crop->id
            ])->get();
            $crop->protocols = json_encode($protocols);

            $items[] = $crop;
        }

        return $this->success(
            $items,
            $message = "Success",
            200
        );
    }

    public function garden_activities(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }

        $gardens = [];
        if ($u->isRole('agent')) {
            $gardens = GardenActivity::where([])
                ->limit(1000)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $gardens = GardenActivity::where(['user_id' => $u->id])
                ->limit(1000)
                ->orderBy('id', 'desc')
                ->get();
        }

        return $this->success(
            $gardens,
            $message = "Success",
            200
        );
    }

    public function my_sacco_membership(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $members = User::where(['id' => $u->id])
            ->limit(1)
            ->orderBy('id', 'desc')
            ->get();
        return $this->success(
            $members,
            $message = "Success",
            200
        );
    }
    public function sacco_members(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $members = User::where(['sacco_id' => $u->sacco_id])
            ->limit(50000)
            ->orderBy('id', 'desc')
            ->get();

        return $this->success(
            $members,
            $message = "Success",
            200
        );
    }
    public function cycles_create(Request $r)
    {

        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $cycle = new Cycle();
        $cycle->name = $r->name;
        $cycle->description = $r->description;
        $cycle->sacco_id = $u->sacco_id;
        $cycle->created_by_id = $u->id;
        $cycle->status = $r->status;
        $cycle->start_date = Carbon::parse($r->start_date);
        $cycle->end_date = Carbon::parse($r->end_date);
        try {
            $cycle->save();
            return $this->success(null, $message = "Success created!", 200);
        } catch (\Throwable $th) {
            return $this->error('Failed to save cycle, because ' . $th->getMessage() . '');
        }
    }

    public function sacco_members_review(Request $r)
    {

        $member = User::find($r->member_id);
        if ($member == null) {
            return $this->error('Member not found.');
        }
        $member->sacco_join_status = $r->sacco_join_status;
        $member->save();
        return $this->success(
            $member,
            $message = "Success",
            200
        );
    }

    public function gardens(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }

        $gardens = [];
        if ($u->isRole('agent')) {
            $gardens = Garden::where([])
                ->limit(1000)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $gardens = Garden::where(['user_id' => $u->id])
                ->limit(1000)
                ->orderBy('id', 'desc')
                ->get();
        }

        return $this->success(
            $gardens,
            $message = "Success",
            200
        );
    }



    public function people(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }

        return $this->success(
            Person::where(['administrator_id' => $u->id])
                ->limit(100)
                ->orderBy('id', 'desc')
                ->get(),
            $message = "Success",
            200
        );
    }
    public function jobs(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }

        return $this->success(
            Job::where([])
                ->orderBy('id', 'desc')
                ->limit(100)
                ->get(),
            $message = "Success",
        );
    }


    public function activity_submit(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        if (
            $r->activity_id == null ||
            $r->farmer_activity_status == null ||
            $r->farmer_comment == null
        ) {
            return $this->error('Some Information is still missing. Fill the missing information and try again.');
        }

        $activity = GardenActivity::find($r->activity_id);

        if ($activity == null) {
            return $this->error('Activity not found.');
        }

        $image = "";
        if (!empty($_FILES)) {
            try {
                $image = Utils::upload_images_2($_FILES, true);
                $image = 'images/' . $image;
            } catch (Throwable $t) {
                $image = "no_image.jpg";
            }
        }

        $activity->photo = $image;
        $activity->farmer_activity_status = $r->farmer_activity_status;
        $activity->farmer_comment = $r->farmer_comment;
        if ($r->activity_date_done != null && strlen($r->activity_date_done) > 2) {
            $activity->activity_date_done = Carbon::parse($r->activity_date_done);
            $activity->farmer_submission_date = Carbon::now();
            $activity->farmer_has_submitted = 'Yes';
        }



        try {
            $activity->save();
            return $this->success(null, $message = "Success created!", 200);
        } catch (\Throwable $th) {
            return $this->error('Failed to save activity, because ' . $th->getMessage() . '');
        }
    }

    public function garden_create(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        if (
            $r->name == null ||
            $r->planting_date == null ||
            $r->crop_id == null
        ) {
            return $this->error('Some Information is still missing. Fill the missing information and try again.');
        }


        $image = "";
        if (!empty($_FILES)) {
            try {
                $image = Utils::upload_images_2($_FILES, true);
                $image = 'images/' . $image;
            } catch (Throwable $t) {
                $image = "no_image.jpg";
            }
        }

        $obj = new Garden();
        $obj->name = $r->name;
        $obj->user_id = $u->id;
        $obj->status = $r->status;
        $obj->production_scale = $r->production_scale;
        $obj->planting_date = Carbon::parse($r->planting_date);
        $obj->land_occupied = $r->planting_date;
        $obj->crop_id = $r->crop_id;
        $obj->details = $r->details;
        $obj->photo = $image;
        $obj->save();


        return $this->success(null, $message = "Success created!", 200);
    }

    public function product_create(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        if (
            $r->name == null ||
            $r->category == null ||
            $r->price == null
        ) {
            return $this->error('Some Information is still missing. Fill the missing information and try again.');
        }

        $image = "";
        if (!empty($_FILES)) {
            try {
                $image = Utils::upload_images_2($_FILES, true);
                $image = 'images/' . $image;
            } catch (Throwable $t) {
                $image = "no_image.jpg";
            }
        }




        $obj = new Product();
        $obj->name = $r->name;
        $obj->administrator_id = $u->id;
        $obj->type = $r->category;
        $obj->details = $r->details;
        $obj->price = $r->price;
        $obj->offer_type = $r->offer_type;
        $obj->state = $r->state;
        $obj->district_id = $r->district_id;
        $obj->subcounty_id = 1;
        $obj->photo = $image;

        try {
            $obj->save();
            return $this->success(null, $message = "Product Uploaded Success!", 200);
        } catch (\Throwable $th) {
            return $this->error('Failed to save product, because ' . $th->getMessage() . '');
            //throw $th;
        }
    }

    public function person_create(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        if (
            $r->name == null ||
            $r->sex == null ||
            $r->subcounty_id == null
        ) {
            return $this->error('Some Information is still missing. Fill the missing information and try again.');
        }

        $image = "";
        if (!empty($_FILES)) {
            try {
                $image = Utils::upload_images_2($_FILES, true);
                $image = 'images/' . $image;
            } catch (Throwable $t) {
                $image = "no_image.jpg";
            }
        }

        $obj = new Person();
        $obj->id = $r->id;
        $obj->created_at = $r->created_at;
        $obj->association_id = $r->association_id;
        $obj->administrator_id = $u->id;
        $obj->group_id = $r->group_id;
        $obj->name = $r->name;
        $obj->address = $r->address;
        $obj->parish = $r->parish;
        $obj->village = $r->village;
        $obj->phone_number = $r->phone_number;
        $obj->email = $r->email;
        $obj->district_id = $r->district_id;
        $obj->subcounty_id = $r->subcounty_id;
        $obj->disability_id = $r->disability_id;
        $obj->phone_number_2 = $r->phone_number_2;
        $obj->dob = $r->dob;
        $obj->sex = $r->sex;
        $obj->education_level = $r->education_level;
        $obj->employment_status = $r->employment_status;
        $obj->has_caregiver = $r->has_caregiver;
        $obj->caregiver_name = $r->caregiver_name;
        $obj->caregiver_sex = $r->caregiver_sex;
        $obj->caregiver_phone_number = $r->caregiver_phone_number;
        $obj->caregiver_age = $r->caregiver_age;
        $obj->caregiver_relationship = $r->caregiver_relationship;
        $obj->photo = $image;
        $obj->save();


        return $this->success(null, $message = "Success registered!", 200);
    }

    public function groups()
    {
        return $this->success(Group::get_groups(), 'Success');
    }


    public function associations()
    {
        return $this->success(Association::where([])->orderby('id', 'desc')->get(), 'Success');
    }

    public function institutions()
    {
        return $this->success(Institution::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function service_providers()
    {
        return $this->success(ServiceProvider::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function counselling_centres()
    {
        return $this->success(CounsellingCentre::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function products()
    {
        return $this->success(Product::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function events()
    {
        return $this->success(Event::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function news_posts()
    {
        return $this->success(NewsPost::where([])->orderby('id', 'desc')->get(), 'Success');
    }



    public function index(Request $r, $model)
    {

        $className = "App\Models\\" . $model;
        $obj = new $className;

        if (isset($_POST['_method'])) {
            unset($_POST['_method']);
        }
        if (isset($_GET['_method'])) {
            unset($_GET['_method']);
        }


        $conditions = [];
        foreach ($_GET as $k => $v) {
            if (substr($k, 0, 2) == 'q_') {
                $conditions[substr($k, 2, strlen($k))] = trim($v);
            }
        }
        $is_private = true;
        if (isset($_GET['is_not_private'])) {
            $is_not_private = ((int)($_GET['is_not_private']));
            if ($is_not_private == 1) {
                $is_private = false;
            }
        }
        if ($is_private) {
            $u = $r->user;
            $administrator_id = $u->id;
            if ($u == null) {
                return $this->error('User not found.');
            }
            $conditions['administrator_id'] = $administrator_id;
        }

        $items = [];
        $msg = "";

        try {
            $items = $className::where($conditions)->get();
            $msg = "Success";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }

        if ($success) {
            return $this->success($items, 'Success');
        } else {
            return $this->error($msg);
        }
    }


    public function delete(Request $r, $model)
    {
        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);


        if ($u == null) {
            return Utils::error([
                'message' => "User not found.",
            ]);
        }


        $className = "App\Models\\" . $model;
        $id = ((int)($r->online_id));
        $obj = $className::find($id);


        if ($obj == null) {
            return Utils::error([
                'message' => "Item already deleted.",
            ]);
        }


        try {
            $obj->delete();
            $msg = "Deleted successfully.";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }


        if ($success) {
            return Utils::success([
                'data' => $obj,
                'message' => $msg
            ]);
        } else {
            return Utils::error([
                'message' => $msg
            ]);
        }
    }


    public function update(Request $r, $model)
    {

        $u = auth('api')->user();
        if ($u == null) {
            return Utils::error([
                'message' => "User not found.",
            ]);
        }

        //image logic




        $className = "App\Models\\" . $model;
        $id = ((int)($r->id));
        $obj = $className::find($id);

        $isEdit = true;
        if ($obj == null) {
            $obj = new $className;
            $isEdit = false;
        }

        if ($isEdit) {
            if (isset($r->my_task)) {
                if ($r->my_task == 'delete') {
                    $obj->delete();
                    return Utils::error([
                        'message' => "Deleted successfully.",
                    ]);
                }
            }
        }

        $table_name = $obj->getTable();
        $cols = Schema::getColumnListing($table_name);



        if (isset($_POST['online_id'])) {
            unset($_POST['online_id']);
        }

        $except = [
            'created_at',
            'updated_at',
            'deleted_at',
            'online_id',
            'id',
            'administrator_id',
            'user_id',
            'created_by',
            'updated_by',
        ];

        foreach ($_POST as $key => $value) {
            if (in_array($key, $except)) {
                continue;
            }
            if (!in_array($key, $cols)) {
                continue;
            }
            $obj->$key = $value;
        }

        if (isset($r->KEY_IMAGE) && $r->KEY_IMAGE != null && !empty($r->KEY_IMAGE)) {
            $KEY_IMAGE = trim($r->KEY_IMAGE);

            if (in_array($KEY_IMAGE, $cols)) {
                if (!empty($_FILES)) {
                    $image = "";
                    try {
                        $image = Utils::upload_images_2($_FILES, true);
                        $image = 'images/' . $image;
                    } catch (Throwable $t) {
                        $image = 'no_image.jpg';
                    }
                    $obj->$KEY_IMAGE = $image;
                }
            }
        }



        $success = false;
        $msg = "";
        if ($isEdit) {
            $msg = "Updated successfully.";
        } else {
            $msg = "Created successfully.";
        }
        try {
            $obj->save();
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }

        //get object
        $obj = $className::find($obj->id);

        if ($success) {
            return Utils::success($obj, $msg);
        } else {
            return Utils::error([
                'message' => $msg
            ]);
        }
    }
}
