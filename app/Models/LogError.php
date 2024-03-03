<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogError extends Model
{
    use HasFactory;
    //Fillable
    protected $fillable = [
        'message',
        'file',
        'line',
        'trace',
        'url',
        'method',
        'input',
        'user_agent',
        'ip'
    ]; 
    
}
