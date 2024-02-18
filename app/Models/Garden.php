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

    //getter for garden_name
    public function getGardenNameAttribute()
    {
        return $this->name;
    }

    public static function boot()
    {
        parent::boot();
        self::created(function ($m) {
        });
    }

    //crop_text
    public function getCropTextAttribute()
    {
        if ($this->crop == null) {
            return 'No Crop';
        }
        return $this->crop->name;
    }

    //belongs to crop
    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }
    //belongs to parish
    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    //getter for parish_text
    public function getParishTextAttribute()
    {
        if ($this->parish == null) {
            return 'No Parish';
        }
        return $this->parish->name_text;
    }

    //appends crop_text
    protected $appends = ['crop_text', 'parish_text'];
}
