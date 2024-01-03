<?php

use App\Models\Loan;
use App\Models\LoanTransaction;
use App\Models\Sacco;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanInterestBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_interest_bills', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Sacco::class);
            $table->foreignIdFor(Loan::class);
            $table->foreignIdFor(LoanTransaction::class);
            $table->integer('year');
            $table->integer('month');
            $table->integer('week');
            $table->integer('day');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_interest_bills');
    }
}
