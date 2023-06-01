<?php

use App\Models\Location;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class);
            $table->text('district')->nullable();
            $table->text('region')->nullable();
            $table->text('type_of_promoter')->nullable();
            $table->text('login')->nullable();
            $table->text('team_leader')->nullable();
            $table->text('client_phone_number')->nullable();
            $table->text('client_activation_momo_code')->nullable();
            $table->text('client_neighborhood')->nullable();
            $table->text('other')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('downloads');
    }
}
