<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    //setter for multiple members
    public function setMembersAttribute($value)
    {
        $this->attributes['members'] = json_encode($value);
    }
    //getter for multiple members
    public function getMembersAttribute($value)
    {
        return $this->attributes['members'] = json_decode($value);
    } 
}
