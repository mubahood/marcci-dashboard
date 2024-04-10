<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContributionProgramRecord extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        static::updated(function ($m) {
            $program = ContributionProgram::find($m->contribution_program_id);
            $program->update_balances();
            return true;
        });

        static::created(function ($m) {
            $program = ContributionProgram::find($m->contribution_program_id);
            $program->update_balances();
        });
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