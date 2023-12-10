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
        $cycle = Cycle::where('sacco_id', $this->id)->where('status', 'Active')->first();
        if ($cycle == null) {
            return 0;
        }
        return Transaction::where([
            'user_id' => $this->administrator_id,
            'cycle_id' => $cycle->id
        ])->sum('amount');
    }

    //active cycle
    public function getActiveCycleAttribute()
    {
        return Cycle::where('sacco_id', $this->id)->where('status', 'Active')->first();
    }

    //appends
    protected $appends = [
        'balance',
        'active_cycle',
        'cycle_text',
        'SAVING',
        'SHARE',
        'LOAN',
        'SHARE_COUNT',
        'LOAN_COUNT',
        'LOAN_REPAYMENT',
        'LOAN_INTEREST',
        'FEE',
        'WITHDRAWAL',
        'FINE',
        'CYCLE_PROFIT',
        'cycle_id',
    ];

    //getter for cycle_text 
    public function getCycleTextAttribute()
    {
        $cycle = Cycle::where('sacco_id', $this->id)->where('status', 'Active')->first();
        if ($cycle == null) {
            return "No active cycle";
        }
        return $cycle->name;
    }

    //getter for cycle_id
    public function getCycleIdAttribute()
    {
        $cycle = Cycle::where('sacco_id', $this->id)->where('status', 'Active')->first();
        if ($cycle == null) {
            return null;
        }
        return $cycle->id;
    }

    public function getCYCLEPROFITAttribute()
    {
        $admin = Sacco::find($this->administrator_id);
        if ($admin == null) {
            return 0;
        }
        if ($this->active_cycle == null) {
            return 0;
        }
        //calculate the total profit
        $total_profit = 0;
    }

    public function getWITHDRAWALAttribute()
    {
        $admin = Sacco::find($this->administrator_id);
        if ($admin == null) {
            return 0;
        }
        if ($this->active_cycle == null) {
            return 0;
        }
        return Transaction::where([
            'user_id' => $admin->id,
            'type' => 'WITHDRAWAL',
            'cycle_id' => $this->active_cycle->id
        ])
            ->sum('amount');
    }

    public function getLOANINTERESTAttribute()
    {
        $admin = Sacco::find($this->administrator_id);
        if ($admin == null) {
            return 0;
        }
        if ($this->active_cycle == null) {
            return 0;
        }
        return Transaction::where([
            'sacco_id' => $this->id,
            'type' => 'LOAN_INTEREST',
            'cycle_id' => $this->active_cycle->id
        ])
            ->sum('amount');
    }

    public function getFINEAttribute()
    {
        $admin = Sacco::find($this->administrator_id);
        if ($admin == null) {
            return 0;
        }
        if ($this->active_cycle == null) {
            return 0;
        }
        return Transaction::where([
            'user_id' => $admin->id,
            'type' => 'FINE',
            'cycle_id' => $this->active_cycle->id
        ])
            ->sum('amount');
    }

    public function getSAVINGAttribute()
    {
        $admin = Sacco::find($this->administrator_id);
        if ($admin == null) {
            return 0;
        }
        if ($this->active_cycle == null) {
            return 0;
        }
        return Transaction::where([
            'user_id' => $admin->id,
            'type' => 'SAVING',
            'cycle_id' => $this->active_cycle->id
        ])
            ->sum('amount');
    }

    public function getFEEAttribute()
    {
        $admin = Sacco::find($this->administrator_id);
        if ($admin == null) {
            return 0;
        }
        if ($this->active_cycle == null) {
            return 0;
        }
        return Transaction::where([
            'user_id' => $admin->id,
            'type' => 'FEE',
            'cycle_id' => $this->active_cycle->id
        ])
            ->sum('amount');
    }

    public function getLOANREPAYMENTAttribute()
    {
        $admin = Sacco::find($this->administrator_id);
        if ($admin == null) {
            return 0;
        }
        if ($this->active_cycle == null) {
            return 0;
        }
        return Transaction::where([
            'user_id' => $admin->id,
            'type' => 'LOAN_REPAYMENT',
            'cycle_id' => $this->active_cycle->id
        ])
            ->sum('amount');
    }

    public function getSHAREAttribute()
    {
        $admin = Sacco::find($this->administrator_id);
        if ($admin == null) {
            return 0;
        }
        if ($this->active_cycle == null) {
            return 0;
        }
        return Transaction::where([
            'user_id' => $admin->id,
            'type' => 'SHARE',
            'cycle_id' => $this->active_cycle->id
        ])
            ->sum('amount');
    }

    public function getLOANAttribute()
    {
        $admin = Sacco::find($this->administrator_id);
        if ($admin == null) {
            return 0;
        }
        if ($this->active_cycle == null) {
            return 0;
        }
        return Transaction::where([
            'sacco_id' => $this->id,
            'type' => 'LOAN',
            'cycle_id' => $this->active_cycle->id
        ])
            ->sum('amount');
    }

    public function getSHARECOUNTAttribute()
    {
        $admin = Sacco::find($this->administrator_id);
        if ($admin == null) {
            return 0;
        }
        if ($this->active_cycle == null) {
            return 0;
        }
        return ShareRecord::where([
            'sacco_id' => $this->id,
            'cycle_id' => $this->active_cycle->id
        ])
            ->sum('number_of_shares');
    }

    public function getLOANCOUNTAttribute()
    {
        $admin = Sacco::find($this->administrator_id);
        if ($admin == null) {
            return 0;
        }
        if ($this->active_cycle == null) {
            return 0;
        }
        return Loan::where([
            'sacco_id' => $this->id,
            'cycle_id' => $this->active_cycle->id
        ])
            ->count();
    }
}
