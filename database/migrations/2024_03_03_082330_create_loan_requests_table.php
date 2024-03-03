<?php

use App\Models\Cycle;
use App\Models\LoanScheem;
use App\Models\Sacco;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_requests', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Sacco::class, 'sacco_id');
            $table->foreignIdFor(User::class, 'applicant_id');
            $table->foreignIdFor(User::class, 'approved_by_id')->nullable();
            $table->foreignIdFor(LoanScheem::class, 'loan_scheem_id');
            $table->foreignIdFor(Cycle::class, 'cycle_id');
            $table->integer('amount');
            $table->text('reason');
            $table->string('status')->default('Pending');
            $table->text('comment')->nullable();
        });
    }
    /*
scheme_name
scheme_description
scheme_initial_interest_type
scheme_initial_interest_flat_amount
scheme_initial_interest_percentage
scheme_bill_periodically
scheme_billing_period
scheme_periodic_interest_type
scheme_periodic_interest_percentage
scheme_periodic_interest_flat_amount
scheme_min_amount
scheme_max_amount
scheme_min_balance
scheme_max_balance


deposited_funds_to_applicant
deducted_funds_from_sacco
principal_loan_transaction_created
first_interest_loan_transaction_created
	

*/
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_requests');
    }
}
