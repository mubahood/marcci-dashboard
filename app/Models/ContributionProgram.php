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
            $m->total_expected = 0;
            $m->total_balance = 0;
            $m->total_collected = 0;
            $m->collected_amount = 0;
            $m->members_contributed = 0;
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
        }
        if (!in_array($model->status, [
            'Active',
            'InActive',
        ])) {
            throw new \Exception("Invalid status", 1);
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

        $logged_in_user = auth()->user();
        if ($logged_in_user == null) {
            throw new \Exception("User not logged in", 1);
        }
        $model->sacco_id = $logged_in_user->sacco_id;
        if ($model->sacco_id == null) {
            throw new \Exception("Sacco not found", 1);
        }
        $sacco = Sacco::find($model->sacco_id);
        if ($sacco == null) {
            throw new \Exception("Sacco not found", 1);
        }


        $model->sacco_id = $sacco->id;

        return $model;
    }

    public static function prepare($model)
    {
        if ($model->periodic_type != 'Weekly' && $model->periodic_type != 'Monthly') {
            return;
        }
        if ($model->prepared == 'Yes') {
            return;
        }
        $active_members = User::where([
            'language' => 'Compulsory',
            'reg_number' => 'Alive',
            'sacco_id' => $model->sacco_id,
        ])->get();

        $program_start_data = Carbon::parse($model->start_date);
        $program_end_data = Carbon::parse($model->end_date);

        if ($program_start_data == null) {
            throw new \Exception("Start date cannot be null.", 1);
        }
        if ($program_end_data == null) {
            throw new \Exception("End date cannot be null.", 1);
        }
        if ($program_start_data > $program_end_data) {
            throw new \Exception("Start date cannot be greater than end date.", 1);
        }

        foreach ($active_members as $key => $member) {
            try {
                self::add_member_to_program($model, $member);
            } catch (\Throwable $th) {
                throw $th;
            }
        }

        //$sql mark as prepared
        DB::table('contribution_programs')
            ->where('id', $model->id)
            ->update([
                'prepared' => 'Yes',
            ]);
    }

    public static function add_member_to_program($program, $member)
    {
        //set unlimited time of execution
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        if ($program->contribution_type !== 'Periodic') {
            return;
        }

        $program_start_data = Carbon::parse($program->start_date);
        $program_end_data   = Carbon::parse($program->end_date);

        if (! $program_start_data || ! $program_end_data) {
            throw new \Exception("Start date and end date cannot be null.", 1);
        }
        if ($program_start_data->gt($program_end_data)) {
            throw new \Exception("Start date cannot be greater than end date.", 1);
        }

        // determine member start
        if (!empty($member->contribution_start_date) && strlen($member->contribution_start_date) > 2) {
            $member_start_data = Carbon::parse($member->contribution_start_date);
        } else {
            $member_start_data = Carbon::now();
        }
        if ($member_start_data->gt($program_end_data)) {
            throw new \Exception("Member start date cannot be greater than program end date.", 1);
        }

        if (! in_array($program->periodic_type, ['Weekly', 'Monthly'], true)) {
            throw new \Exception("Invalid periodic type", 1);
        }

        // initialize first period boundary
        if ($program->periodic_type === 'Weekly') {
            $start_of_period_date = $program_start_data->copy()->startOfWeek();
        } else {
            $start_of_period_date = $program_start_data->copy()->startOfMonth();
        }

        while ($start_of_period_date->lte($program_end_data)) {

            if ($program->periodic_type === 'Weekly') {
                $end_of_period_date = $start_of_period_date->copy()->endOfWeek();
                $period_name        = strtoupper($start_of_period_date->format('Y-m-d'));
            } else {
                $end_of_period_date = $start_of_period_date->copy()->endOfMonth();
                $period_name        = strtoupper($start_of_period_date->format('F-Y'));
            }

            // skip periods outside program window
            if (
                $end_of_period_date->lt($program_start_data)
                || $start_of_period_date->gt($program_end_data)
            ) {
                $start_of_period_date = $program->periodic_type === 'Weekly'
                    ? $start_of_period_date->addWeek()
                    : $start_of_period_date->addMonth();
                continue;
            }

            // skip if before member’s own start
            if ($member_start_data->gt($start_of_period_date)) {
                $start_of_period_date = $program->periodic_type === 'Weekly'
                    ? $start_of_period_date->addWeek()
                    : $start_of_period_date->addMonth();
                continue;
            }

            // --- no mass assignment here! ---
            $contribution_program_record = ContributionProgramRecord::where([
                'member_id'               => $member->id,
                'contribution_program_id' => $program->id,
                'period_name'             => $period_name,
            ])->first();

            $isEdit = true;
            if (! $contribution_program_record) {
                $isEdit = false;
                $contribution_program_record = new ContributionProgramRecord();
                $contribution_program_record->teasurer_id        = null;
                $contribution_program_record->is_paid            = 'No';
                $contribution_program_record->month_name         = $period_name;
                $contribution_program_record->period_name        = $period_name;
                $contribution_program_record->payment_date       = null;
                $contribution_program_record->paid_amount        = 0;
                $contribution_program_record->type               = $program->periodic_type;
            }

            // explicit assignment of every field:
            $contribution_program_record->sacco_id             = $program->sacco_id;
            $contribution_program_record->member_id            = $member->id;
            $contribution_program_record->year                 = $start_of_period_date->format('Y');
            $contribution_program_record->week_number          = $start_of_period_date->format('W');
            $contribution_program_record->month_number         = $start_of_period_date->format('m');
            $contribution_program_record->contribution_program_id = $program->id;
            $contribution_program_record->description          = $program->name
                . ($program->periodic_type === 'Weekly'
                    ? " - Week: {$period_name}"
                    : " - Month: {$period_name}");
            $contribution_program_record->period_range_start   = $start_of_period_date->format('Y-m-d');
            $contribution_program_record->period_range_end     = $end_of_period_date->format('Y-m-d');

            if ($program->amount_to_use === 'PERSONALIZED_AMOUNT') {
                $contribution_program_record->amount = $member->personalized_contribution_amount;
            } else {
                $contribution_program_record->amount = $program->amount_per_member_value;
            }

            $contribution_program_record->save();


            // move to next period
            $start_of_period_date = $program->periodic_type === 'Weekly'
                ? $start_of_period_date->addWeek()
                : $start_of_period_date->addMonth();
        }
    }



    public function update_balances()
    {
        if (($this->contribution_type == 'Periodic')) {
            $this->total_expected = DB::table('contribution_program_records')
                ->where('contribution_program_id', $this->id)
                ->sum('amount');
        }

        $this->total_collected = DB::table('contribution_program_records')
            ->where('contribution_program_id', $this->id)
            ->sum('paid_amount');

        $this->total_balance = (int)($this->total_expected) - (int)($this->total_collected);

        DB::table($this->getTable())
            ->where('id', $this->id)
            ->update([
                'total_expected' => $this->total_expected,
                'total_collected' => $this->total_collected,
                'total_balance' => $this->total_balance,
            ]);
    }


    public static function update_pending_programs()
    {
        foreach (
            ContributionProgram::where([
                'prepared' => 'No'
            ])->get() as $key => $value
        ) {
            ContributionProgram::prepare($value);
        }
    }
}
