<?php

namespace App\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sacco extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            throw new \Exception("Cannot delete Sacco");
        });
        self::created(function ($m) {
            $u = User::find($m->administrator_id);
            if ($u == null) {
                throw new \Exception("Sacco Administrator not found");
            }
            $u->sacco_id = $m->id;
            $u->user_type = 'Admin';
            $u->status = 'Active';
            $u->sacco_join_status = 'Approved';
            $u->save();
        });

        //updated
        self::updated(function ($m) {
            $u = User::find($m->administrator_id);
            if ($u == null) {
                throw new \Exception("Sacco Administrator not found");
            }
            $u->sacco_id = $m->id;
            $u->user_type = 'Admin';
            $u->status = 'Active';
            $u->sacco_join_status = 'Approved';
            $u->save();
        });

        self::creating(function ($m) {

            return $m;
        });



        self::updating(function ($m) {

            $u = User::find($m->administrator_id);
            if ($u == null) {
                throw new \Exception("Sacco Administrator not found");
            }
            $u->sacco_id = $m->id;
            $u->save();

            return $m;
        });
    }

    //balance
    public function getBalanceAttribute()
    {
        return Transaction::where('user_id', $this->administrator_id)->sum('amount');
    }

    //appends
    protected $appends = ['balance'];
}
