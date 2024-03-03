<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogErrorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_errors', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('message')->nullable();
            $table->text('file')->nullable();
            $table->integer('line')->nullable();
            $table->text('trace')->nullable();
            $table->text('url')->nullable();
            $table->text('method')->nullable();
            $table->text('input')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('ip')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log_errors');
    }
}
