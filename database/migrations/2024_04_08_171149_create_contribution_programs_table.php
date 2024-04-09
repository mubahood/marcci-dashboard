<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContributionProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contribution_programs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('sacco_id');
            $table->bigInteger('total_expected')->default(0)->nullable();
            $table->bigInteger('total_collected')->default(0)->nullable();
            $table->bigInteger('total_balance')->default(0)->nullable();
            $table->string('amount_per_member_type')->default(0)->nullable();
            $table->bigInteger('amount_per_member_value')->default(0)->nullable();
            $table->text('name')->nullable();
            $table->string('contribution_type')->nullable();
            $table->string('periodic_type')->nullable();
            $table->string('new_members_billing_type')->nullable();
            $table->text('details')->nullable();
            $table->string('status')->nullable()->default('Active');
            $table->string('public_type')->nullable()->default('Public');
            $table->string('membership_type')->nullable();
            $table->text('members')->nullable();
            $table->text('treasurers')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contribution_programs');
    }
}
