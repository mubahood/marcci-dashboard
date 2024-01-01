<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Loan extends Model
{
    use HasFactory;

    //get balance
    public function getBalanceAttribute()
    {
        return LoanTransaction::where('loan_id', $this->id)->sum('amount');
    }

    //boot
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $user = User::find($model->user_id);
            if ($user == null) {
                throw new Exception("User not found");
            }
            return self::prepare_loan($model);
        });

        //creatd
        static::created(function ($loan) {
            try {
                Loan::deduct_funds_from_sacco_account($loan);
            } catch (\Exception $e) {
                $loan->delete();
                throw new \Exception($e->getMessage());
            }
            try {
                Loan::deposit_funds_to_applicant_account($loan);
            } catch (\Exception $e) {
                $loan->delete();
                throw new \Exception($e->getMessage());
            }
            try {
                Loan::create_principal_loan_transaction($loan);
            } catch (\Exception $e) {
                $loan->delete();
                throw new \Exception($e->getMessage());
            }
            try {
                Loan::create_first_interest_loan_transaction($loan);
            } catch (\Exception $e) {
                $loan->delete();
                throw new \Exception($e->getMessage());
            }
        });
    }

    public static function prepare_loan($model)
    {
        $u = User::find($model->user_id);
        if ($u == null) {
            throw new Exception('User account found.', 1);
        }


        $loan_scheem = LoanScheem::find($model->loan_scheem_id);
        if ($loan_scheem == null) {
            throw new Exception('Loan scheem not found.');
        }

        $total_deposit = Transaction::where([
            'user_id' => $u->id,
        ])
            ->where('amount', '>', 0)
            ->sum('amount');


        if ($loan_scheem->min_balance > $total_deposit) {
            throw new Exception('You have not saved enough money to apply for this loan. You need to save at least UGX ' . number_format($loan_scheem->min_balance) . ' to apply for this loan.');
        }

        $oldLoans = Loan::where([
            'user_id' => $u->id,
            'is_fully_paid' => 'No',
        ])->get();

        if (count($oldLoans) > 0) {
            //throw new Exception('You have an existing loan that is not fully paid. You cannot apply for another loan until you have fully paid the existing loan.');
        }

        $sacco = Sacco::find($u->sacco_id);
        if ($sacco == null) {
            throw new Exception('Sacco not found.');
        }

        if ($loan_scheem->max_amount < $model->amount) {
            throw new Exception('You cannot apply for a loan of more than UGX ' . number_format($loan_scheem->max_amount) . '.');
        }

        if ($sacco->balance < $model->amount) {
            throw new Exception('The sacco does not have enough money to lend you UGX ' . number_format($r->amount) . '.');
        }

        //get active cycle
        $cycle = Cycle::where('sacco_id', $u->sacco_id)->where('status', 'Active')->first();
        if ($cycle == null) {
            throw new Exception("No active cycle found.");
        }

        $logged_user = auth()->user();
        $model->cycle_id = $cycle->id;
        $model->sacco_id = $u->sacco_id;
        $model->user_id = $u->id;
        $model->loan_scheem_id = $loan_scheem->id;
        $model->balance = $model->amount;
        $model->is_fully_paid = 'No';
        $model->scheme_name = $loan_scheem->name;
        $model->scheme_description = $loan_scheem->description;
        $model->scheme_initial_interest_type = $loan_scheem->initial_interest_type;
        $model->scheme_initial_interest_flat_amount = $loan_scheem->initial_interest_flat_amount;
        $model->scheme_initial_interest_percentage = $loan_scheem->initial_interest_percentage;
        $model->scheme_bill_periodically = $loan_scheem->bill_periodically;
        $model->scheme_billing_period = $loan_scheem->billing_period;
        $model->scheme_periodic_interest_type = $loan_scheem->periodic_interest_type;
        $model->scheme_periodic_interest_percentage = $loan_scheem->periodic_interest_percentage;
        $model->scheme_periodic_interest_flat_amount = $loan_scheem->periodic_interest_flat_amount;
        $model->scheme_min_amount = $loan_scheem->min_amount;
        $model->scheme_max_amount = $loan_scheem->max_amount;
        $model->scheme_min_balance = $loan_scheem->min_balance;
        $model->scheme_max_balance = $loan_scheem->max_balance;
        return $model;
    }

    public static function deduct_funds_from_sacco_account($model)
    {
        if ($model->deducted_funds_from_sacco == 'Yes') {
            return;
        }
        $sacco = Sacco::find($model->sacco_id);
        if ($sacco == null) {
            throw new Exception('Sacco not found.');
        }

        $u = User::find($model->user_id);
        if ($u == null) {
            throw new Exception('User account found.');
        }
        $loan_scheem = LoanScheem::find($model->loan_scheem_id);
        if ($loan_scheem == null) {
            throw new Exception('Loan scheem not found.');
        }
        $loan = $model;

        $amount = abs($model->amount);
        $sacco_transactions = new Transaction();
        $sacco_transactions->user_id = $sacco->administrator_id;
        $sacco_transactions->source_user_id = $model->user_id;
        $sacco_transactions->sacco_id = $sacco->id;
        $sacco_transactions->type = 'LOAN';
        $sacco_transactions->source_type = 'Loan';
        $sacco_transactions->source_mobile_money_number = null;
        $sacco_transactions->source_mobile_money_transaction_id = null;
        $sacco_transactions->source_bank_account_number = null;
        $sacco_transactions->source_bank_transaction_id = null;
        $sacco_transactions->desination_bank_account_number = null;
        $sacco_transactions->desination_type = 'User';
        $sacco_transactions->desination_mobile_money_number = $u->phone_number;
        $sacco_transactions->desination_mobile_money_transaction_id = null;
        $sacco_transactions->desination_bank_transaction_id = null;
        $sacco_transactions->amount = (-1 * (abs($amount)));
        $sacco_transactions->description = "Loan Disbursement of UGX " . number_format($amount) . " to {$u->phone_number} - $u->name. Loan Scheem: {$loan_scheem->name}. Reference: {$loan->id}.";
        $sacco_transactions->details = "Loan Disbursement of UGX " . number_format($amount) . " to {$u->phone_number} - $u->name. Loan Scheem: {$loan_scheem->name}. Reference: {$loan->id}.";
        try {
            $sacco_transactions->save();
            //set deducted_funds_from_sacco = 'Yes' use DB
            DB::table('loans')
                ->where('id', $model->id)
                ->update(['deducted_funds_from_sacco' => 'Yes']);
        } catch (\Throwable $th) {
            throw new Exception('Failed to save transaction, because ' . $th->getMessage() . '');
        }
    }


    public static function deposit_funds_to_applicant_account($model)
    {
        if ($model->deposited_funds_to_applicant == 'Yes') {
            return;
        }
        $sacco = Sacco::find($model->sacco_id);
        if ($sacco == null) {
            throw new Exception('Sacco not found.');
        }

        $u = User::find($model->user_id);
        if ($u == null) {
            throw new Exception('User account found.');
        }
        $loan_scheem = LoanScheem::find($model->loan_scheem_id);
        if ($loan_scheem == null) {
            throw new Exception('Loan scheem not found.');
        }
        $loan = $model;


        try {

            $amount = abs($model->amount);
            $receiver_transactions = new Transaction();
            $receiver_transactions->user_id = $u->id;
            $receiver_transactions->source_user_id = $sacco->administrator_id;
            $receiver_transactions->type = 'LOAN';
            $receiver_transactions->source_type = 'LOAN';
            $receiver_transactions->source_mobile_money_number = null;
            $receiver_transactions->source_mobile_money_transaction_id = null;
            $receiver_transactions->source_bank_account_number = null;
            $receiver_transactions->source_bank_transaction_id = null;
            $receiver_transactions->desination_bank_account_number = null;
            $receiver_transactions->desination_type = 'User';
            $receiver_transactions->desination_mobile_money_number = $u->phone_number;
            $receiver_transactions->desination_mobile_money_transaction_id = null;
            $receiver_transactions->desination_bank_transaction_id = null;
            $receiver_transactions->amount = $amount;
            $receiver_transactions->description = "Received Loan of UGX " . number_format($amount) . " from  $sacco->name -  Sacco Loan Scheem: {$loan_scheem->name}. Reference: {$loan->id}.";
            $receiver_transactions->details = "Received Loan of UGX " . number_format($amount) . " from  $sacco->name -  Sacco Loan Scheem: {$loan_scheem->name}. Reference: {$loan->id}.";

            $receiver_transactions->save();
            DB::table('loans')
                ->where('id', $model->id)
                ->update(['deposited_funds_to_applicant' => 'Yes']);
        } catch (\Throwable $th) {
            throw new Exception('Failed to save transaction, because ' . $th->getMessage() . '');
        }
    }

    public static function create_principal_loan_transaction($model)
    {
        if ($model->principal_loan_transaction_created == 'Yes') {
            return;
        }
        $sacco = Sacco::find($model->sacco_id);
        if ($sacco == null) {
            throw new Exception('Sacco not found.');
        }

        $u = User::find($model->user_id);
        if ($u == null) {
            throw new Exception('User account found.');
        }
        $loan_scheem = LoanScheem::find($model->loan_scheem_id);
        if ($loan_scheem == null) {
            throw new Exception('Loan scheem not found.');
        }
        $loan = $model;
        try {
            $amount = abs($model->amount);
            $LoanTransaction = new LoanTransaction();
            $LoanTransaction->user_id = $u->id;
            $LoanTransaction->loan_id = $loan->id;
            $LoanTransaction->sacco_id = $sacco->id;
            $amount = abs($amount);
            $LoanTransaction->amount = -1 * $amount;
            $LoanTransaction->balance = 0;
            $LoanTransaction->description = "Borrowed UGX " . number_format($amount) . " from {$sacco->name} - {$loan_scheem->name}. Reference: {$loan->id}.";
            $LoanTransaction->save();
            DB::table('loans')
                ->where('id', $model->id)
                ->update(['principal_loan_transaction_created' => 'Yes']);
        } catch (\Throwable $th) {
            throw new Exception('Failed to create loan transaction, because ' . $th->getMessage() . '');
        }
    }



    public static function create_first_interest_loan_transaction($model)
    {
        if ($model->first_interest_loan_transaction_created == 'Yes') {
            return;
        }
        $sacco = Sacco::find($model->sacco_id);
        if ($sacco == null) {
            throw new Exception('Sacco not found.');
        }

        $u = User::find($model->user_id);
        if ($u == null) {
            throw new Exception('User account found.');
        }
        $loan_scheem = LoanScheem::find($model->loan_scheem_id);
        if ($loan_scheem == null) {
            throw new Exception('Loan scheem not found.');
        }
        $loan = $model;
        try {
            $amount = abs($model->amount);
            $initialBalance = $loan->balance;
            if ($loan_scheem->initial_interest_type == 'Flat') {
                $initialBalance =  $loan->initial_interest_flat_amount;
            } else {
                $_amount = abs($amount);
                $initialBalance =  (($loan_scheem->initial_interest_percentage / 100)) * $_amount;
            }
            $initialBalance = abs($initialBalance);
            $initialInterestTransaction = new LoanTransaction();
            $initialInterestTransaction->user_id = $u->id;
            $initialInterestTransaction->loan_id = $loan->id;
            $initialInterestTransaction->sacco_id = $sacco->id;
            $initialInterestTransaction->amount = -1 * $initialBalance;
            $initialInterestTransaction->balance = $initialBalance;
            $initialInterestTransaction->description = "Initial Interest of UGX " . number_format($initialBalance) . " for {$sacco->name} - {$loan_scheem->name}. Reference: {$loan->id}.";
            $initialInterestTransaction->save();
            DB::table('loans')
                ->where('id', $model->id)
                ->update(['first_interest_loan_transaction_created' => 'Yes']);
        } catch (\Throwable $th) {
            throw new Exception('Failed to create loan transaction, because ' . $th->getMessage() . '');
        }
    }


    //append for user_text
    protected $appends = ['user_text'];

    //getter for user_text
    public function getUserTextAttribute()
    {
        $user = User::find($this->user_id);
        if ($user == null) {
            return "Unknown";
        }
        return $user->name;
    }
}
