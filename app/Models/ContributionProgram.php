<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ContributionProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'sacco_id',
        'name',
        'contribution_type',
        'periodic_type',
        'amount_per_member_type',
        'amount_per_member_value',
        'start_date',
        'end_date',
        'status',
        'members',
        'treasurers',
        'total_expected',
        'total_balance',
        'total_collected',
        'collected_amount',
        'members_contributed',
        'prepared',
        'details',
        'new_members_billing_type',
        'public_type',
        'membership_type',
        'target_amount',
        'amount_to_use',
        'period_name',
    ];

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
        // Set reasonable limits to prevent infinite loops and memory issues
        set_time_limit(300); // 5 minutes max
        ini_set('memory_limit', '512M'); // Reasonable limit
        
        if ($program->contribution_type !== 'Periodic') {
            return;
        }

        $program_start_data = Carbon::parse($program->start_date);
        $program_end_data   = Carbon::parse($program->end_date);
        
        // Safeguard: Maximum iterations to prevent infinite loops
        $maxIterations = 1000;
        $iterations = 0;

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

        while ($start_of_period_date->lte($program_end_data) && $iterations < $maxIterations) {
            $iterations++;

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

            // Calculate amount based on program settings
            $amount = $program->amount_to_use === 'PERSONALIZED_AMOUNT'
                ? $member->personalized_contribution_amount ?? 0
                : $program->amount_per_member_value;
            
            $description = $program->name
                . ($program->periodic_type === 'Weekly'
                    ? " - Week: {$period_name}"
                    : " - Month: {$period_name}");
            
            // Use firstOrCreate to prevent duplicate records (atomic operation)
            $contribution_program_record = ContributionProgramRecord::firstOrCreate(
                // Unique identifier fields
                [
                    'member_id'               => $member->id,
                    'contribution_program_id' => $program->id,
                    'period_name'             => $period_name,
                ],
                // Default values for new records only
                [
                    'sacco_id'             => $program->sacco_id,
                    'teasurer_id'          => null,
                    'year'                 => $start_of_period_date->format('Y'),
                    'week_number'          => $start_of_period_date->format('W'),
                    'month_number'         => $start_of_period_date->format('m'),
                    'is_paid'              => 'No',
                    'month_name'           => $period_name,
                    'payment_date'         => null,
                    'paid_amount'          => 0,
                    'type'                 => $program->periodic_type,
                    'description'          => $description,
                    'period_range_start'   => $start_of_period_date->format('Y-m-d'),
                    'period_range_end'     => $end_of_period_date->format('Y-m-d'),
                    'amount'               => $amount,
                ]
            );
            
            // Update amount if it changed (for existing records)
            if ($contribution_program_record->amount != $amount) {
                $contribution_program_record->amount = $amount;
                $contribution_program_record->save();
            }


            // move to next period
            $start_of_period_date = $program->periodic_type === 'Weekly'
                ? $start_of_period_date->addWeek()
                : $start_of_period_date->addMonth();
        }
        
        // Check if we hit the iteration limit
        if ($iterations >= $maxIterations) {
            throw new \Exception("Program period too long. Maximum $maxIterations periods allowed. Please reduce the date range.");
        }
    }



    public function update_balances()
    {
        // Use database transaction with row locking to prevent race conditions
        return DB::transaction(function () {
            // Lock row for update to prevent concurrent modifications
            $program = self::lockForUpdate()->find($this->id);
            
            if (!$program) {
                return false;
            }
            
            if ($program->contribution_type == 'Periodic') {
                $program->total_expected = DB::table('contribution_program_records')
                    ->where('contribution_program_id', $program->id)
                    ->sum('amount');
            }

            $program->total_collected = DB::table('contribution_program_records')
                ->where('contribution_program_id', $program->id)
                ->where('is_paid', 'Yes')
                ->sum('paid_amount');

            $program->total_balance = (int)($program->total_expected) - (int)($program->total_collected);

            // Use Eloquent save() instead of raw query to fire model events
            $program->save();
            
            return $program;
        });
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
