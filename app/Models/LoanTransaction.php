<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
            $model->sacco_id = $user->sacco_id;
            return $model;
        });

        //creatd
        static::created(function ($model) {
            $model->balance = LoanTransaction::where('loan_id', $model->loan_id)->sum('amount');
            $model->save();
        });
        static::updated(function ($model) {
            $loan = Loan::find($model->loan_id);
            if ($loan == null) {
                throw new Exception("Loan not found");
            }
            $loan->balance = LoanTransaction::where('loan_id', $loan->id)->sum('amount');
            $loan->save();
            return $model;
        });
    }

    //belongs to loan
    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }
}
