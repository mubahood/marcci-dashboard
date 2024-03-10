<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRequest extends Model
{
    use HasFactory;

    //boot
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            //get pending loan for the applicant
            $pending_loan = LoanRequest::where('applicant_id', $model->applicant_id)
                ->where('status', 'Pending')
                ->first();
            if ($pending_loan != null) { 
;                throw new \Exception("You have a pending loan request. Please wait for it to be approved or rejected.");
            }
        });

        //created
        static::created(function ($model) {
            //create loan transaction
            //sacco admin
            $sacco = Sacco::find($model->sacco_id);
            $admin = null;
            if ($sacco != null) {
                $admin = $sacco->getAdmin();
            }
            if ($admin == null) {
                throw new \Exception("Sacco admin account not found.");
            }
            $sacco_admin = $admin;

            if ($sacco_admin != null) {
                //send email to sacco admin
                //title include money, amount, applicant name and application id
                $applicant = User::find($model->applicant_id);
                if ($applicant == null) {
                    return;
                }
                $title = 'Loan Request - ' . $model->amount . ' - ' . $applicant->name . ' - #' . $model->id;
                //message in html to admin 
                $message = '<p>Dear ' . $sacco_admin->name . ',</p>';
                $message .= '<p>A loan request has been made by ' . $applicant->name . ' for ' . $model->amount . '.</p>';
                $message .= '<p>Reason: ' . $model->reason . '</p>';
                $message .= '<p>Application ID: ' . $model->id . '</p>';
                $message .= '<p>Open the application to approve or reject the request.</p>';
                $message .= '<p>Thank you.</p>';
                try {
                    Utils::mail_sender([
                        'email' => $sacco_admin->email,
                        'name' => $sacco_admin->name,
                        'subject' => env('APP_NAME') . ' ' . $title,
                        'body' => $message
                    ]);
                } catch (Exception $e) {
                    LogError::create([
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                        'url' => request()->url(),
                        'method' => request()->method(),
                        'input' => json_encode(request()->input()),
                        'user_agent' => request()->userAgent(),
                        'ip' => request()->ip()
                    ]);
                }
            }
        });
    }
}
