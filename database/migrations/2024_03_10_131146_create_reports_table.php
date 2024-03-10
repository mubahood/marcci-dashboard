<?php

use App\Models\Cycle;
use App\Models\Sacco;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(User::class, 'created_by_user_id');
            $table->foreignIdFor(Sacco::class, 'sacco_id');
            $table->foreignIdFor(Cycle::class, 'cycle_id')->nullable();
            $table->string('title');
            $table->string('status')->default('Pending');
            $table->string('report_type')->default('Report');
            $table->string('period_type')->default('Report');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('SAVING', 15, 2)->default(0);
            $table->decimal('SHARE_COUNT', 15, 2)->default(0);
            $table->decimal('SHARE', 15, 2)->default(0);
            $table->decimal('LOAN_BALANCE', 15, 2)->default(0);
            $table->decimal('LOAN_TOTAL_AMOUNT', 15, 2)->default(0);
            $table->decimal('LOAN_COUNT', 15, 2)->default(0);
            $table->decimal('LOAN_INTEREST', 15, 2)->default(0);
            $table->decimal('LOAN_REPAYMENT', 15, 2)->default(0);
            $table->decimal('FEE', 15, 2)->default(0);
            $table->decimal('WITHDRAWAL', 15, 2)->default(0);
            $table->decimal('CYCLE_PROFIT', 15, 2)->default(0);
            $table->decimal('FINE', 15, 2)->default(0);
            $table->text('transactions_data')->nullable();
            $table->text('loan_transactions_data')->nullable();
            $table->text('users_data')->nullable();
            $table->text('loans_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
}
