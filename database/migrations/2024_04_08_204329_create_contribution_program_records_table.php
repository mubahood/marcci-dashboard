<?php

use App\Models\ContributionProgram;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContributionProgramRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contribution_program_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('sacco_id');
            $table->bigInteger('member_id');
            $table->bigInteger('teasurer_id');
            $table->integer('year');
            $table->integer('week_number');
            $table->integer('month_number');
            $table->foreignIdFor(ContributionProgram::class);
            $table->bigInteger('amount');
            $table->string('is_paid')->default('No');
            $table->string('month_name');
            $table->string('type');
            $table->text('description')->nullable();
            $table->text('details')->nullable();
            $table->date('payment_date')->nullable();
            $table->date('period_range_start')->nullable();
            $table->date('period_range_end')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contribution_program_records');
    }
}
