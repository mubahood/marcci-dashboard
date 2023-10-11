<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Garden extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'garden_name',
        'garden_size',
        'ownership',
        'planting_date',
        'harvest_date',
        'variety_id',
        'seed_class',
        'certified_seller',
        'name_of_seller',
        'seller_location',
        'seller_contact',
        'purpose_of_seller',
        'user_id',
        'crop_id',
    ];

    public static function boot()
    {
        parent::boot();
        self::created(function ($m) {
           
        });
    }

 
}
