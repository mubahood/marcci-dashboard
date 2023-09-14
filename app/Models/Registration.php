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

}
