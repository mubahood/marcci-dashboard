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
        });
        self::created(function ($m) {
            $u = User::find($m->administrator_id);
            if ($u == null) {
                throw new \Exception("Sacco Administrator not found");
            }
            $u->sacco_id = $m->id;
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
}
