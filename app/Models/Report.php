<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    //creating
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $user = User::find($model->created_by_user_id);
            if ($user == null) {
                throw new Exception("User not found");
            }
            $model->sacco_id = $user->sacco_id;
            return self::prepare($model);
        });

        //updating
        static::updating(function ($model) {
            $user = User::find($model->created_by_user_id);
            if ($user == null) {
                throw new Exception("User not found");
            }
            $model->sacco_id = $user->sacco_id;
            if ($model->status == 'Pending') {
                return self::prepare($model);
            }
        });
    }

    //static prepare
    public static function prepare($data)
    {
        $user = User::find($data['created_by_user_id']);
        if ($user == null) {
            throw new Exception("User not found");
        }
        $data['sacco_id'] = $user->sacco_id;

        $title = '';

        if ($data->report_type == 'General') {
            $title = 'Financial Report';
        } else if ($data->report_type == 'Member') {
            $u = User::find($data->user_id);
            if ($u == null) {
                throw new Exception("User not found");
            }
            $title = 'Financial Report - ' . $u->name;
        }

        if ($data->period_type == 'Cycle') {
            $cycle = Cycle::find($data->cycle_id);
            if ($cycle == null) {
                throw new Exception("Cycle not found");
            }
            $title .= ' - for th cycle: ' . $cycle->name;
            $data->start_date = Carbon::now();
            $data->end_date = Carbon::now();
        } else if ($data->period_type == 'Period') {
            $data->start_date = Carbon::parse($data->start_date);
            $data->end_date = Carbon::parse($data->end_date);
            $title .= ' - for the period: ' . $data->start_date->format('d M, Y') . ' to ' . $data->end_date->format('d M, Y');
        }
        $data->title = $title;
        $data->status = 'Prepared';

        $conds = [
            'sacco_id' => $user->sacco_id,
        ];

        if ($data->period_type == 'Period') {
            $conds['created_at'] = [
                '>=', $data->start_date->format('Y-m-d') . ' 00:00:00',
                '<=', $data->end_date->format('Y-m-d') . ' 23:59:59',
            ];
        } else if ($data->period_type == 'Cycle') {
            $cycle = Cycle::find($data->cycle_id);
            $conds['cycle_id'] = $cycle->id;
        }

        if ($data->report_type == 'General') {
            $data->balance = Transaction::where($conds)->sum('amount');
            $data->TOTAL_SAVING = Transaction::where($conds)->where('type', 'SAVING')->sum('amount');
            $data->SHARE_COUNT = User::where('sacco_id', $user->sacco_id)->count();
            $data->SHARE = Transaction::where($conds)->where('type', 'SHARE')->sum('amount');
            $data->LOAN_BALANCE = LoanTransaction::where($conds)->sum('amount');
            $data->LOAN_TOTAL_AMOUNT = Loan::where($conds)->sum('amount');
            $data->LOAN_COUNT = Loan::where($conds)->count();
            $data->LOAN_INTEREST = LoanTransaction::where($conds)->where('type', 'LOAN_INTEREST')->sum('amount');
            $data->LOAN_REPAYMENT = LoanTransaction::where($conds)->where('type', 'LOAN_REPAYMENT')->sum('amount');
            $data->FEE = Transaction::where($conds)->where('type', 'FEE')->sum('amount');
            $data->WITHDRAWAL = Transaction::where($conds)->where('type', 'WITHDRAWAL')->sum('amount');
            $data->CYCLE_PROFIT = Transaction::where($conds)->where('type', 'CYCLE_PROFIT')->sum('amount');
            $data->FINE = Transaction::where($conds)->where('type', 'FINE')->sum('amount');
            $data->transactions_data = Transaction::where($conds)->get();
            $data->loan_transactions_data = LoanTransaction::where($conds)->get();
            $data->users_data = User::where('sacco_id', $user->sacco_id)->get();
            $data->loans_data = Loan::where($conds)->get();

            $data->transactions_data = json_encode(Transaction::where($conds)->where('user_id', $u->id)->get());
            $data->loan_transactions_data = json_encode(LoanTransaction::where($conds)->where('user_id', $u->id)->get());
            $data->users_data = json_encode(User::where('sacco_id', $user->sacco_id)->get());
            $data->loans_data = json_encode(Loan::where($conds)->where('user_id', $u->id)->get());
        } else if ($data->report_type == 'Member') {
            $u = User::find($data->user_id);
            if ($u == null) {
                throw new Exception("User not found");
            }
            $data->balance = Transaction::where($conds)->where('user_id', $u->id)->sum('amount');
            $data->TOTAL_SAVING = Transaction::where($conds)->where('type', 'SAVING')->where('user_id', $u->id)->sum('amount');
            $data->SHARE_COUNT = 0;
            $data->SHARE = Transaction::where($conds)->where('type', 'SHARE')->where('user_id', $u->id)->sum('amount');
            $data->LOAN_BALANCE = LoanTransaction::where($conds)->where('user_id', $u->id)->sum('amount');
            $data->LOAN_TOTAL_AMOUNT = Loan::where($conds)->where('user_id', $u->id)->sum('amount');
            $data->LOAN_COUNT = Loan::where($conds)->where('user_id', $u->id)->count();
            $data->LOAN_INTEREST = LoanTransaction::where($conds)->where('type', 'LOAN_INTEREST')->where('user_id', $u->id)->sum('amount');
            $data->LOAN_REPAYMENT = LoanTransaction::where($conds)->where('type', 'LOAN_REPAYMENT')->where('user_id', $u->id)->sum('amount');
            $data->FEE = Transaction::where($conds)->where('type', 'FEE')->where('user_id', $u->id)->sum('amount');
            $data->WITHDRAWAL = Transaction::where($conds)->where('type', 'WITHDRAWAL')->where('user_id', $u->id)->sum('amount');
            $data->CYCLE_PROFIT = Transaction::where($conds)->where('type', 'CYCLE_PROFIT')->where('user_id', $u->id)->sum('amount');
            $data->FINE = Transaction::where($conds)->where('type', 'FINE')->where('user_id', $u->id)->sum('amount');
            $data->transactions_data = json_encode(Transaction::where($conds)->where('user_id', $u->id)->get());
            $data->loan_transactions_data = json_encode(LoanTransaction::where($conds)->where('user_id', $u->id)->get());
            $data->users_data = json_encode(User::where('sacco_id', $user->sacco_id)->get());
            $data->loans_data = json_encode(Loan::where($conds)->where('user_id', $u->id)->get());
        }

        /* 
        $grid->column('period_type', __('Period type'));
        $grid->column('start_date', __('Start date'));
        $grid->column('end_date', __('End date'));
        $grid->column('balance', __('Balance'));
        $grid->column('TOTAL_SAVING', __('TOTAL_SAVING'));
        $grid->column('SHARE_COUNT', __('SHARE COUNT'));
        $grid->column('SHARE', __('SHARE'));
        $grid->column('LOAN_BALANCE', __('LOAN BALANCE'));
        $grid->column('LOAN_TOTAL_AMOUNT', __('LOAN TOTAL AMOUNT'));
        $grid->column('LOAN_COUNT', __('LOAN COUNT'));
        $grid->column('LOAN_INTEREST', __('LOAN INTEREST'));
        $grid->column('LOAN_REPAYMENT', __('LOAN REPAYMENT'));
        $grid->column('FEE', __('FEE'));
        $grid->column('WITHDRAWAL', __('WITHDRAWAL'));
        $grid->column('CYCLE_PROFIT', __('CYCLE PROFIT'));
        $grid->column('FINE', __('FINE'));
        $grid->column('transactions_data', __('Transactions data'));
        $grid->column('loan_transactions_data', __('Loan transactions data'));
        $grid->column('users_data', __('Users data'));
        $grid->column('loans_data', __('Loans data'));
        $grid->column('is_processed', __('Is processed'));
*/
        return $data;
    }
}
