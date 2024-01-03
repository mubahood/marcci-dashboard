<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanScheem extends Model
{
    use HasFactory;

    //boot
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            

            //get active cycle
            $cycle = Cycle::where('sacco_id', $model->sacco_id)->where('status', 'Active')->first();
            if ($cycle == null) {
                throw new Exception("No active cycle found");
            }  
            $model->sacco_id = $model->sacco_id;
            return $model;
        });
    }
}
