<?php

namespace App\Models;

use Dflydev\DotAccessData\Util;
use Encore\Admin\Form\Field\BelongsToMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as RelationsBelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'username',
        'password',
        'phone_number',
        'sacco_id',
        'user_type',
        'status',
        'address',
        'date_of_birth',
        'sex',
        'language',
        'sacco_join_status',
    ];

    //getter first_name
    public function getFirstNameAttribute($value)
    {
        if (!isset($this->attributes['name'])) {
            return ucfirst(strtolower($value));
        }
        if (!isset($this->attributes['last_name'])) {
            return ucfirst(strtolower($value));
        }
        if (!isset($this->attributes['first_name'])) {
            return ucfirst(strtolower($value));
        }
        $full_name = $this->attributes['first_name'] . ' ' . $this->attributes['last_name'];
        if ($full_name != $this->attributes['name']) {

            $sql = "UPDATE users SET name = '" . addslashes($full_name) . "' WHERE id = " . $this->id;
            DB::statement($sql);
        }
        return ucfirst(strtolower($value));
    }

    //boot
    protected static function boot()
    {
        parent::boot();

        //creating
        static::creating(function ($model) {
            $model->name = $model->first_name . ' ' . $model->last_name;
            $model = self::do_prepare($model);
        });

        static::created(function ($model) {
            /* try {
                Utils::send_sms($model->phone_number, "Your MobiSave account has been created. Download the app from https://play.google.com/store/apps/details?id=ug.digisave");
            } catch (\Throwable $th) {
                //save error
                LogError::create([
                    'message' => $th->getMessage(),
                    'file' => $th->getFile(),
                    'line' => $th->getLine(),
                    'trace' => $th->getTraceAsString(),
                    'url' => request()->url(),
                    'method' => request()->method(),
                    'input' => json_encode(request()->all()),
                    'user_agent' => request()->header('User-Agent'),
                    'ip' => request()->ip()
                ]);
            } */
            if ($model->language != 'None') {
                $active_programs = ContributionProgram::where('sacco_id', $model->sacco_id)
                    ->where('status', 'Active')
                    ->get();
                foreach ($active_programs as $program) {
                    try {
                        ContributionProgram::add_member_to_program($program, $model);
                    } catch (\Throwable $th) {
                        //throw $th; 
                    }
                }
            }
        });
        //updating
        static::updating(function ($model) {
            //get another user with the same email
            if (
                ($model->email != null) &&
                strlen($model->email) > 5
            ) {
                $user = User::where('email', $model->email)
                    ->where('id', '!=', $model->id)
                    ->first();
                if ($user != null) {
                    throw new \Exception("Email already exists " . $model->email);
                }
            }

            if (
                ($model->phone_number != null) &&
                strlen($model->phone_number) > 5
            ) {
                //check if phone number exists
                $user = User::where('phone_number', $model->phone_number)
                    ->where('id', '!=', $model->id)
                    ->first();
                if ($user != null) {
                    // throw new \Exception("Phone number already exists");
                }
            }

            $model->name = $model->first_name . ' ' . $model->last_name;
            //check usting username as email

            //twitter



            if (
                ($model->email != null) &&
                strlen($model->email) > 5
            ) {
                $user = User::where('username', $model->email)
                    ->where('id', '!=', $model->id)
                    ->first();
                if ($user != null) {
                    throw new \Exception("Username already exists");
                }
            }


            $model = self::do_prepare($model);
        });
    }

    public static function do_prepare($model)
    {
        $model->name = $model->first_name . ' ' . $model->last_name;

        // $model->username should be less than 25 characters
        if (strlen($model->username) > 25) {
            throw new \Exception("Username should be less than 25 characters");
        }

        if ($model->password == null || strlen($model->password) < 4) {
            $model->password = $model->username;
            $model->password = password_hash($model->password, PASSWORD_DEFAULT);
        }



        $username = null;
        if (
            ($model->phone_number != null) &&
            strlen($model->phone_number)  > 5
        ) {
            $username = $model->phone_number;
        }

        if ($username == null) {
            //try email
            if (
                ($model->email != null) &&
                strlen($model->email) > 5
            ) {
                $username = $model->email;
            }
        }

        //check $username
        if ($username == null) {
            $username = $model->first_name . '' . $model->last_name .
                rand(1000, 9999);
        }

        //check if username exists
        $u = User::where('username', $username)
            ->where('id', '!=', $model->id)
            ->first();
        if ($u != null) {
            $username = $model->first_name . '' . $model->last_name .
                rand(1000000, 9999000);
        }
        $model->username = $username;

        //None

        if ($model->should_be_validated == 'Yes') {

            if ($model->website != 'Root') {
            }

            $parent = User::find($model->linkedin);

            if ($parent != null) {
                if ($parent->website == 'Father') {
                    if ($parent->sex != 'Male') {
                        $parent->website = 'Father';
                        // throw new \Exception("Father must be male but is #".$parent->sex);
                    }
                }
                if ($parent->website == 'Mother') {
                    if ($parent->sex != 'Female') {
                        $parent->website = 'Mother';
                        // throw new \Exception("Mother must be Female"); 
                    }
                }
                $model->twitter = $parent->first_name . ' ' . $parent->last_name;
            } else {
                $model->reg_number = 'Alive';
                $model->language = 'Compulsory';
            }

            if ($model->reg_number != 'Alive') {
                $model->website = 'None';
            }

            $logged_in_user = auth()->user();
            if ($logged_in_user == null) {
                throw new \Exception("Logged in user not found");
            }
            $model->sacco_id = $logged_in_user->sacco_id;
        }

        $number_of_admins_in_this_sacco = User::where('sacco_id', $model->sacco_id)
            ->where('is_admin', 'Yes')
            ->count();
        if ($number_of_admins_in_this_sacco == 0) {
            $model->is_admin = 'Yes';
        }

        return $model;
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }


    public function getAvatarAttribute($avatar)
    {
        if (url()->isValidUrl($avatar)) {
            return $avatar;
        }

        //check if is null or empty
        if ($avatar == null || strlen($avatar) < 1) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }

        //check if $avatar has word image in it
        if (strpos($avatar, 'image') !== false) {
            //add image to the url

        } else {
            $avatar = 'images/' . $avatar;
        }
        return url('storage/' . $avatar);
        $disk = config('admin.upload.disk');

        if ($avatar && array_key_exists($disk, config('filesystems.disks'))) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }

        $default = config('admin.default_avatar') ?: '/assets/images/user.jpg';

        return admin_asset($default);
    }





    //getter for name
    public function getUserTextAttribute()
    {
        //merge first name and last name
        return $this->first_name . ' ' . $this->last_name;
    }

    //getter for balance
    public function getBalanceAttribute()
    {
        return Transaction::where('user_id', $this->id)->sum('amount');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function sacco()
    {
        return $this->belongsTo(Sacco::class, 'sacco_id');
    }

    //getter for name
    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    protected $appends = [
        'balance',
        'name',
        'user_text',
        'SAVING',
        'SHARE',
        'LOAN',
        'LOAN_COUNT',
        'LOAN_REPAYMENT',
        'LOAN_INTEREST',
        'FEE',
        'WITHDRAWAL',
        'CYCLE_PROFIT',
    ];

    //getter for CYCLE_PROFIT
    public function getCYCLEPROFITAttribute()
    {
        $sacco = Sacco::find($this->sacco_id);
        if ($sacco == null) {
            return 0;
        }
        if ($sacco->active_cycle == null) {
            return 0;
        }
        return Transaction::where([
            'user_id' => $this->id,
            'type' => 'CYCLE_PROFIT',
            'cycle_id' => $sacco->active_cycle->id
        ])
            ->sum('amount');
    }


    //getter for WITHDRAWAL
    public function getWITHDRAWALAttribute()
    {
        $sacco = Sacco::find($this->sacco_id);
        if ($sacco == null) {
            return 0;
        }
        if ($sacco->active_cycle == null) {
            return 0;
        }

        return Transaction::where([
            'user_id' => $this->id,
            'type' => 'WITHDRAWAL',
            'cycle_id' => $sacco->active_cycle->id
        ])
            ->sum('amount');
    }

    //getter for FEE
    public function getFEEAttribute()
    {
        $sacco = Sacco::find($this->sacco_id);
        if ($sacco == null) {
            return 0;
        }
        if ($sacco->active_cycle == null) {
            return 0;
        }

        return Transaction::where([
            'user_id' => $this->id,
            'type' => 'FEE',
            'cycle_id' => $sacco->active_cycle->id
        ])
            ->sum('amount');
    }

    //getter for LOAN_INTEREST
    public function getLOANINTERESTAttribute()
    {
        $sacco = Sacco::find($this->sacco_id);
        if ($sacco == null) {
            return 0;
        }
        if ($sacco->active_cycle == null) {
            return 0;
        }

        return LoanTransaction::where([
            'user_id' => $this->id,
            'type' => 'LOAN_INTEREST',
            'cycle_id' => $sacco->active_cycle->id
        ])
            ->sum('amount');
    }

    //GETTER FOR LOAN_REPAYMENT
    public function getLOANREPAYMENTAttribute()
    {
        $sacco = Sacco::find($this->sacco_id);
        if ($sacco == null) {
            return 0;
        }
        if ($sacco->active_cycle == null) {
            return 0;
        }

        return LoanTransaction::where(
            'user_id',
            $this->id
        )
            ->where(
                'cycle_id',
                $sacco->active_cycle->id
            )
            ->where('amount', '>', 0)
            ->sum('amount');
    }

    //getter for SAVING
    public function getSAVINGAttribute()
    {
        $sacco = Sacco::find($this->sacco_id);
        if ($sacco == null) {
            return 0;
        }
        if ($sacco->active_cycle == null) {
            return 0;
        }

        return Transaction::where([
            'user_id' => $this->id,
            'type' => 'SAVING',
            'cycle_id' => $sacco->active_cycle->id
        ])
            ->sum('amount');
    }

    //getter for LOAN
    public function getLOANAttribute()
    {
        $sacco = Sacco::find($this->sacco_id);
        if ($sacco == null) {
            return 0;
        }
        if ($sacco->active_cycle == null) {
            return 0;
        }

        return LoanTransaction::where([
            'user_id' => $this->id,
            /*             'type' => 'LOAN', */
            'cycle_id' => $sacco->active_cycle->id
        ])
            ->sum('amount');
    }

    //LOAN_COUNT
    public function getLOAN_COUNTAttribute()
    {
        $sacco = Sacco::find($this->sacco_id);
        if ($sacco == null) {
            return 0;
        }
        if ($sacco->active_cycle == null) {
            return 0;
        }

        return Loan::where([
            'user_id' => $this->id,
            'cycle_id' => $sacco->active_cycle->id
        ])
            ->count();
    }
    //LOAN_COUNT
    public function getLOANCOUNTAttribute()
    {
        $sacco = Sacco::find($this->sacco_id);
        if ($sacco == null) {
            return 0;
        }
        if ($sacco->active_cycle == null) {
            return 0;
        }

        return LoanTransaction::where([
            'user_id' => $this->id,
            'cycle_id' => $sacco->active_cycle->id
        ])
            ->count();
    }

    //getter for SHARE
    public function getSHAREAttribute()
    {
        $sacco = Sacco::find($this->sacco_id);
        if ($sacco == null) {
            return 0;
        }
        if ($sacco->active_cycle == null) {
            return 0;
        }

        return Transaction::where([
            'user_id' => $this->id,
            'type' => 'SHARE',
            'cycle_id' => $sacco->active_cycle->id
        ])
            ->sum('amount');
    }

    public function isAdmin()
    {

        return true;
    }

    //get dropdowndata
    public static function getDropdownData($conds)
    {
        $data = User::where($conds)->get();
        $result = [];
        foreach ($data as $item) {
            $result[$item->id] = $item->name . " " . $item->phone_number;
        }
        return $result;
    }
}
