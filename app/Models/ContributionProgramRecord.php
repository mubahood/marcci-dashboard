<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContributionProgramRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'created_at',
        'updated_at',
        'sacco_id',
        'member_id',
        'teasurer_id',
        'year',
        'week_number',
        'month_number',
        'contribution_program_id',
        'amount',
        'is_paid',
        'month_name',
        'type',
        'description',
        'details',
        'payment_date',
        'period_range_start',
        'period_range_end',
        'period_name',
        'paid_amount',
    ];

    public static function boot()
    {
        parent::boot();

        static::updated(function ($m) {
            $program = ContributionProgram::find($m->contribution_program_id);
            if ($program != null) {
                $program->update_balances();
            }
            return true;
        });

        //updating
        static::deleting(function ($m) {
            $program = ContributionProgram::find($m->contribution_program_id);
            if ($program != null) {
                $program->update_balances();
            }
        });

        //updating
        static::updating(function ($m) {
            if ($m->is_paid != "Yes") {
                $m->paid_amount = 0;
            }
            $m = self::doPrepare($m);
        });

        //creating
        static::creating(function ($m) {
            if ($m->is_paid != "Yes") {
                $m->paid_amount = 0;
            }
            $m = self::doPrepare($m);
        });

        static::created(function ($m) {
            $program = ContributionProgram::find($m->contribution_program_id);
            if ($program != null) {
                $program->update_balances();
            }
        });
    }

    //doPrepare
    public static function doPrepare($m)
    {
        if ($m->period_range_start == null || $m->period_range_start == "") {
            throw new \Exception("Period range start is required.");
        }
        $period_range_start = Carbon::parse($m->period_range_start);
        if ($period_range_start == null) {
            throw new \Exception("Period range start is required.");
        }

        if ($m->year == null || strlen($m->year) < 2) {
            $m->year = $period_range_start->format("Y");
        }
        //week_number
        if ($m->week_number == null || strlen($m->week_number) < 2) {
            $m->week_number = $period_range_start->format("W");
        }
        //month_number
        if ($m->month_number == null || strlen($m->month_number) < 2) {
            $m->month_number = $period_range_start->format("m");
        }

        $contribution_program = ContributionProgram::find($m->contribution_program_id);
        if ($contribution_program == null) {
            throw new \Exception("Contribution program is required.");
        }

        $period_name = strtoupper($period_range_start->format('Y-m-d'));

        //month_name
        if ($m->month_name == null || strlen($m->month_name) < 2) {
            $m->month_name = $period_name;
        }
        if ($m->type == null || strlen($m->type) < 2) {
            $m->type = $contribution_program->periodic_type;
        }

        //if description
        if ($m->description == null || strlen($m->description) < 2) {
            $m->description = $contribution_program->name . " - " . $period_name;
        }

        //period_range_start
        if ($m->period_range_start == null || strlen($m->period_range_start) < 2) {
            $m->period_range_start = $period_range_start->format('Y-m-d');
        }

        //period_range_end
        if ($m->period_range_end == null || strlen($m->period_range_end) < 2) {
            if ($contribution_program->periodic_type == "Weekly") {
                $period_range_end = Carbon::parse($m->period_range_start)->addDays(6);
            } else if ($contribution_program->periodic_type == "Monthly") {
                $period_range_end = Carbon::parse($m->period_range_start)->addMonth(1);
            }
        }
        //period_name
        if ($m->period_name == null || strlen($m->period_name) < 2) {
            $m->period_name = $period_name;
        }

        //if amount
        if ($m->amount == null || strlen($m->amount) < 2) {
            $m->amount = $contribution_program->amount;
        }
        
        //if amount 
        if ($m->amount == null || strlen($m->amount) < 2) {
            $m->amount = 0;
        }

        //if is_paid
        if ($m->is_paid == null || strlen($m->is_paid) < 2) {
            $m->is_paid = "No";
        }
        //if not paid
        if ($m->is_paid == "No") {
            $m->paid_amount = 0;
        }

        return $m;
    }

    protected $appends = ['member_text', 'teasurer_text', 'contribution_program_text'];

    public function getMemberTextAttribute()
    {
        $u = User::find($this->member_id);
        if ($u == null) {
            return "N/A";
        }
        return $u->name;
    }
    public function getTeasurerTextAttribute()
    {
        $u = User::find($this->teasurer_id);
        if ($u == null) {
            return "N/A";
        }
        return $u->name;
    }
    public function getContributionProgramTextAttribute()
    {
        $u = ContributionProgram::find($this->contribution_program_id);
        if ($u == null) {
            return "N/A";
        }
        return $u->name;
    }
}
/* 
  String created_at = "";
  String updated_at = "";
  String sacco_id = ""; 
  String member_id = "";
  String  = "";
  String  = "";
  String year = "";
  String week_number = "";
  String month_number = "";
  String  = "";
  String  = "";
  String amount = "";
  String is_paid = "";
  String month_name = "";
  String type = "";
  String description = "";
  String details = "";
  String payment_date = "";
  String period_range_start = "";
  String period_range_end = ""; */