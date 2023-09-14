<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GardenActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'garden_id',
        'user_id',
        'crop_activity_id',
        'activity_name',
        'activity_description',
        'activity_date_to_be_done',
        'activity_due_date',
        'farmer_has_submitted',
        'farmer_activity_status',
        'farmer_submission_date',
        'farmer_comment',
        'agent_id',
        'agent_names',
        'agent_has_submitted',
        'agent_activity_status',
        'agent_comment',
        'agent_submission_date',
        'activity_date_done',
    ];
}
