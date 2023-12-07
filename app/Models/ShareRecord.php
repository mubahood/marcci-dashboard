<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareRecord extends Model
{
    use HasFactory;
    /* 
 				
	
source_mobile_money_number	
source_mobile_money_transaction_id	
source_bank_account_number	
source_bank_transaction_id	
desination_type	
desination_mobile_money_number	
desination_mobile_money_transaction_id	
desination_bank_account_number	
desination_bank_transaction_id		
	
details	
balance	
 

*/
    public function processTansactions()
    {
        $userTransaction = new Transaction();
        $userTransaction->user_id = $this->user_id;
        $userTransaction->source_user_id = $this->created_by_id;
        $userTransaction->sacco_id = $this->sacco_id;
        $userTransaction->type = 'Share Purchase';
        $userTransaction->source_type = 'Share Purchase';
        $userTransaction->source_mobile_money_number = null;
        $userTransaction->amount = $this->total_amount;
        //Make explanation of the transaction to be the description of the share record
        $userTransaction->description = "Puchase of " . $this->number_of_shares . " shares at " . $this->single_share_amount . " each for a total of " . $this->total_amount;
        $userTransaction->save();
        $userTransaction->balance = Transaction::where('user_id', $userTransaction->user_id)->sum('amount');
        //Add the transaction to the share record

        $sacco = Sacco::find($this->sacco_id);
        //sacco transaction
        $saccoTransaction = new Transaction();
        $saccoTransaction->user_id = $sacco->administrator_id;
        $saccoTransaction->source_user_id = $userTransaction->user_id;
        $saccoTransaction->sacco_id = $this->sacco_id;  
        $saccoTransaction->type = 'Share Purchase';
        $saccoTransaction->source_type = 'Share Purchase';
        $saccoTransaction->source_mobile_money_number = null;
        $saccoTransaction->amount = $this->total_amount;
        //Make explanation of the transaction to be the description of the share record
        $saccoTransaction->description = "Puchase of " . $this->number_of_shares . " shares at " . $this->single_share_amount . " each for a total of " . $this->total_amount;
        $saccoTransaction->save();
        $saccoTransaction->balance = Transaction::where('user_id', $saccoTransaction->user_id)->sum('amount');
        
        $saccoTransaction->save(); 
    }
    /* 
    "id" => 1
    "created_at" => "2023-12-07 02:16:29"
    "updated_at" => "2023-12-07 02:16:29"
    "user_id" => 2
    "single_share_amount" => 25000
    "number_of_shares" => 2
    "total_amount" => 50000
    "cycle_id" => 2
    "sacco_id" => 2
    "" => 2
    "description" => "Test"
*/
    //booot
    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            throw new \Exception("Cannot delete Share Record");
        });
        self::creating(function ($m) {
            $m->created_by_id = auth()->user()->id;
            $m->sacco_id = auth()->user()->sacco_id;
            $m->cycle_id = Cycle::where('sacco_id', auth()->user()->sacco_id)->where('status', 'Active')->first()->id;
            $sacco = Sacco::find(auth()->user()->sacco_id);
            $m->single_share_amount = $sacco->share_price;
            $m->total_amount = $m->single_share_amount * $m->number_of_shares;
            return $m;
        });

        //updated
        self::updating(function ($m) {
            $m->total_amount = $m->single_share_amount * $m->number_of_shares;
            return $m;
        });

        self::creating(function ($m) {

            return $m;
        });
    }
}
