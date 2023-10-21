<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    //boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = Administrator::find($model->user_id);
            if ($user == null) {
                throw new Exception("User not found");
            }
            $model->sacco_id = $user->sacco_id;
            return $model;
        });
    }
}
