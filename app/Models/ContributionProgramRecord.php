<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContributionProgramRecord extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        static::updated(function ($m) {
            $program = ContributionProgram::find($m->contribution_program_id);
            $program->update_balances();
            return true;
        });

        static::created(function ($m) {
            $program = ContributionProgram::find($m->contribution_program_id);
            $program->update_balances();
        });
    }

    protected $appends = ['member_text'];

    public function getMemberTextAttribute()
    {
        $u = User::find($this->member_id);
        if($u == null){
            return "N/A";
        }
        return $u->name;
    }
}
