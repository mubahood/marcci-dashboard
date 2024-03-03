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
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use Notifiable;

    //boot
    protected static function boot()
    {
        parent::boot();
        static::created(function ($model) {
            try {
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
            }
        });
        //updating
        static::updating(function ($model) {
            //get another user with the same email
            $user = User::where('email', $model->email)
                ->where('id', '!=', $model->id)
                ->first();
            if ($user != null) {
                throw new \Exception("Email already exists");
            }
            //check if phone number exists
            $user = User::where('phone_number', $model->phone_number)
                ->where('id', '!=', $model->id)
                ->first();
            if ($user != null) {
                throw new \Exception("Phone number already exists");
            }
            //check usting username as email
            $user = User::where('username', $model->email)
                ->where('id', '!=', $model->id)
                ->first();
            if ($user != null) {
                throw new \Exception("Username already exists");
            } 
        });
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
}
