<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ContributionProgram extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();
        static::creating(function ($m) {
            $m->prepared = "No";
            $m = self::validate($m);
            return true;
        });

        static::updating(function ($m) {
            $m = self::validate($m);
            return true;
        });

        static::created(function ($m) {
            $m->prepared = "No";
            self::prepare($m);
        });
        static::deleting(function ($m) {
            ContributionProgramRecord::where([
                'contribution_program_id' => $m->id
            ])->delete();
        });
    }

    public function setMembersAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['members'] = json_encode($value);
        }
    }
    public function getMembersAttribute($value)
    {
        if ($value == null || strlen($value) < 2) {
            return [];
        }
        return json_decode($value);
    }




    public function setTreasurersAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['treasurers'] = json_encode($value);
        }
    }
    public function getTreasurersAttribute($value)
    {
        if ($value == null || strlen($value) < 2) {
            return [];
        }
        return json_decode($value);
    }

    public static function validate($model)
    {
        $model->amount_per_member_value = abs((int)$model->amount_per_member_value);
        if (!in_array($model->amount_per_member_type, [
            'Specific',
            'Any'
        ])) {
            throw new \Exception("Invalid amount per member type", 1);
        }
        if (!in_array($model->contribution_type, [
            'Periodic',
            'Open'
        ])) {
            throw new \Exception("Invalid contribution type", 1);
        }

        if ($model->contribution_type == 'Periodic') {
            if (!in_array($model->periodic_type, [
                'Weekly',
                'Monthly'
            ])) {
                throw new \Exception("Invalid periodic type", 1);
            }
            if (!in_array($model->new_members_billing_type, [
                'MemberRegisterDate',
                'ContributionStartDate',
                'SpecificDate'
            ])) {
                throw new \Exception("Invalid new members billing type", 1);
            }
        }
        if (!in_array($model->status, [
            'Active',
            'InActive',
        ])) {
            throw new \Exception("Invalid status", 1);
        }
        if (!in_array($model->membership_type, [
            'All',
            'Specific',
        ])) {
            throw new \Exception("Invalid membership type", 1);
        }
        $start_date = Carbon::parse($model->start_date);
        $end_date = Carbon::parse($model->end_date);

        $days = $start_date->diffInDays($end_date);
        if ($days == 0) {
            throw new \Exception("Period should be at least more than 1 day.", 1);
        }

        if ($start_date->gt($end_date)) {
            throw new \Exception("Start date cannot be greater than end date.", 1);
        }

        if ($model->amount_per_member_type == 'Specific') {
            $model->amount_per_member_value = abs((int)$model->amount_per_member_value);

            if ($model->amount_per_member_value < 1) {
                throw new \Exception("Enter valid amount per member value", 1);
            }
        }
    }

    public static function prepare($model)
    {
        if ($model->prepared == 'Yes') {
            return;
        }

        $created_at = Carbon::parse($model->created_at);
        $sacco = Sacco::find($model->sacco_id);
        if ($sacco == null) {
            throw new \Exception("sacco not found", 1);
        }
        $amount_per_member_value = 0;
        if ($model->amount_per_member_type == 'Specific') {
            $amount_per_member_value = abs((int)$model->amount_per_member_value);
        }


        if (($model->contribution_type == 'Periodic')) {

            if ($amount_per_member_value < 1) {
                throw new \Exception("Enter valid amount per member value", 1);
            }

            $start_date = Carbon::parse($model->start_date);
            $end_date = Carbon::parse($model->end_date);

            $counter = 0;
            do {
                if ($counter > 10000) {
                    break;
                }
                $counter++;


                $conds = [];
                $from = $start_date->copy();
                $w = $from->week();
                $m = $from->month;
                $month_name = $from->monthName;
                $y = $from->year;
                $conds['year'] = $y;
                $conds['contribution_program_id'] = $model->id;
                if ($model->periodic_type == "Weekly") {
                    $conds['week_number'] = $w;
                    $start_date->addWeek();
                } else {
                    $conds['month_number'] = $m;
                    $start_date->addMonth();
                }
                if (($start_date->gt($end_date))) {
                    break;
                }
                $to = $start_date->copy();
                foreach (User::where('sacco_id', $sacco->id)->get() as $member) {
                    if ($sacco->administrator_id == $member->id) {
                        continue;
                    }
                    $conds['member_id'] = $member->id;
                    $exist = ContributionProgramRecord::where($conds)->first();
                    if ($exist) {
                        continue;
                    }
                    $record = new ContributionProgramRecord();
                    $record->sacco_id = $model->sacco_id;
                    $record->member_id = $member->id;
                    $record->teasurer_id = $sacco->administrator_id;
                    $record->year = $y;
                    $record->week_number = $w;
                    $record->month_number = $m;
                    $record->contribution_program_id = $model->id;
                    $record->amount = $amount_per_member_value;
                    $record->is_paid = 'No';
                    $record->month_name = $month_name;
                    $record->type = $model->periodic_type;
                    $record->description = null;
                    $record->details = null;
                    $record->payment_date = null;
                    $record->period_range_start = $from->format('Y-m-d');
                    $record->period_range_end = $to->format('Y-m-d');
                    $record->save();
                }
            } while ($start_date->lt($end_date));
        }
        $table_name = $model->getTable();
        $sql = "UPDATE $table_name SET prepared = 'Yes' WHERE id = $model->id";
        DB::update($sql);
        $model->update_balances();
    }

    public function update_balances()
    {
        if (($this->contribution_type == 'Periodic')) {
            $this->total_expected = ContributionProgramRecord::where([
                'contribution_program_id' => $this->id
            ])->sum('amount');
        }

        $this->total_collected = ContributionProgramRecord::where([
            'contribution_program_id' => $this->id,
            'is_paid' => 'Yes'
        ])->sum('amount');
        $this->total_balance = $this->total_expected - $this->total_collected;
        $this->save();
    }


    public static function update_pending_programs()
    {
        foreach (ContributionProgram::where([
            'prepared' => 'No'
        ])->get() as $key => $value) {
            ContributionProgram::prepare($value);
        }
    }
}
