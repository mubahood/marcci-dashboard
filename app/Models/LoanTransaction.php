<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LoanTransaction extends Model
{
    use HasFactory;

    //creatd
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $user = User::find($model->user_id);
            if ($user == null) {
                throw new Exception("User not found");
            }
            
            //get active cycle
            $cycle = Cycle::where('sacco_id', $user->sacco_id)->where('status', 'Active')->first();
            if ($cycle == null) {
                throw new Exception("No active cycle found");
            }
            $model->cycle_id = $cycle->id;

           /*  if($model->type != 'LOAN'){
                if($model->type != 'LOAN_INTEREST'){
                    throw new Exception("Invalid loan type.");
                }
            } */

            $model->sacco_id = $user->sacco_id;
            return $model;
        });

        //creatd
        static::created(function ($model) {
            $loan = Loan::find($model->loan_id);
            if ($loan == null) {
                throw new Exception("Loan not found");
            }
            $loan_balance = LoanTransaction::where('loan_id', $loan->id)->sum('amount');
            $loan->balance = $loan_balance;
            //check if loan is fully paid
            if ($loan_balance == $loan->amount) {
                $loan->is_fully_paid = 'Yes';
            } else {
                $loan->is_fully_paid = 'No';
            }


            $loan->save();
            DB::table('loan_transactions')->where('id', $model->id)->update(['balance' => $loan_balance]);
        });
    }

    //belongs to loan
    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }
}
