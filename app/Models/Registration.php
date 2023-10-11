<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'level_of_education',
        'marital_status',
        'number_of_dependants',
        'farmers_group',
        'farming_experience',
        'production_scale',
        'company_information',
        'owner_name',
        'phone_number',
        'registration_number',
        'registration_date',
        'physical_address',
        'certificate_and_compliance',
        'service_provider_name',
        'email_address',
        'postal_address',
        'services_offered',
        'district_sub_county',
        'logo',
        'certificate_of_incorporation',
        'license',

    ];

  //on creating a registration the user role is set to 2
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registration) {
          
        }          
        );

        static::updated(function ($registration) {
         
            //change the role of the basic user to that of the seed producer if approved
            if($registration->isDirty('status') && $registration->status == 1){
               
                if($registration->category == 'farmer' || $registration->category == 'seed producer'){
                  
                    AdminRoleUser::where([
                        'user_id' => $registration->user_id
                    ])->delete();
                    $new_role = new AdminRoleUser();
                    $new_role->user_id = $registration->user_id;
                    $new_role->role_id = 4;
                    $new_role->save();
                }
            }
        }  
                    
            );
        }


}
