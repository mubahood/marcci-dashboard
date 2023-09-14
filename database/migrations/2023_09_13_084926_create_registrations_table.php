<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('category')->nullable();
            $table->string('level_of_education')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('number_of_dependants')->nullable();
            $table->string('farmers group')->nullable();
            $table->string('farming_experience')->nullable();
            $table->string('production_scale')->nullable();
            $table->string('company_information')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('registration_date')->nullable();
            $table->string('physical_address')->nullable();
            $table->string('certificate_and_compliance')->nullable();
            $table->string('service_provider_name')->nullable();
            $table->string('email_address')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('services_offered')->nullable();
            $table->string('district_sub_county')->nullable();
            $table->string('logo')->nullable();
            $table->string('certificate_of_incorporation')->nullable();
            $table->string('license')->nullable();
            
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
        Schema::dropIfExists('registrations');
    }
}
