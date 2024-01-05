<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_models', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('name')->nullable();
            $table->text('theme')->nullable();
            $table->text('photo')->nullable();
            $table->text('details')->nullable();
            $table->text('venue_name')->nullable();
            $table->text('venue_address')->nullable();
            $table->text('gps_latitude')->nullable();
            $table->text('gps_longitude')->nullable();
            $table->date('event_date')->nullable();
            $table->time('event_time')->nullable();
            $table->text('event_duration')->nullable();
            $table->text('event_type')->nullable();
            $table->text('ticket_types')->nullable();
            $table->text('is_free')->nullable();
            $table->string('status')->nullable()->default('Upcoming');
            $table->text('event_organizer')->nullable();
            $table->text('rsvp_phone_1')->nullable();
            $table->text('rsvp_phone_2')->nullable();
            $table->text('rsvp_email')->nullable();
            $table->text('rsvp_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_models');
    }
}
