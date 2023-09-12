<?php

namespace App\Models;

use Encore\Admin\Form\Field\BelongsToMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as RelationsBelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject ;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;



class User extends Authenticatable implements JWTSubject
{
    use HasFactory; 
    use Notifiable;
 
    protected $fillable = [
        'name',
        'last_name',
        'first_name',
        'username',
        'email',
        'gender',
        'phone_number',
        'district_sub_county',
        'village',
        'password',
       
       
    ];  
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }


    public function campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }
 
    public function programs()
    {
        return $this->hasMany(UserHasProgram::class, 'user_id');
    }
 
}
