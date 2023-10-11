<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancialRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('financial_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('garden_id');
            $table->unsignedBigInteger('user_id');
            $table->string('category')->nullable();
            $table->decimal('amount', 8, 2)->nullable();
            $table->string('payment_method')->nullable();//cash, cheque, bank transfer, mobile money
            $table->string('recipient')->nullable();
            $table->string('description')->nullable();
            $table->string('receipt')->nullable(); //image
            $table->date('date')->nullable();
            $table->string('remarks')->nullable();

            $table->foreign('garden_id')->references('id')->on('gardens')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); 



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('financial_records');
    }
}