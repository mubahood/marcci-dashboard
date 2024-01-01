<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPreperationColsToLoans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->string("deposited_funds_to_applicant")->nullable()->default("No");
            $table->string("deducted_funds_from_sacco")->nullable()->default("No");
            $table->string("principal_loan_transaction_created")->nullable()->default("No");
            $table->string("first_interest_loan_transaction_created")->nullable()->default("No");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            //
        });
    }
}
