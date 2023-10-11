<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'garden_id',
        'user_id',
        'category', //income or expense
        'date',
        'amount',
        'payment_method', //cash, cheque, bank transfer, mobile money
        'recipient', 
        'description',
        'receipt', //image
        'remarks'
        
    ];
}
