<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contribution extends Model
{
    use HasFactory;

    public function update_self()
    {
        $sacco = Sacco::find($this->sacco_id);

        $total_collection = Transaction::where([
            'source_bank_transaction_id' => $this->id,
            'type' => 'CONTRIBUTION',
            'user_id' => $sacco->administrator_id,
        ])->sum('amount');


        $this->collected_amount = $total_collection;

        $members_contributed_ids = [];

        foreach (Transaction::where('source_bank_transaction_id', $this->id)->get() as $key => $value) {
            if (!in_array($value->user_id, $members_contributed_ids)) {
                if ($sacco->administrator_id == $value->user_id) {
                    continue;
                }
                $members_contributed_ids[] = $value->user_id;
            }
        }
        $this->members_contributed = json_encode($members_contributed_ids);
        $this->save();
    }
}
