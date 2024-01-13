<?php

use App\Models\Sacco;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContributionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Sacco::class);
            $table->text('name')->nullable();
            $table->text('target_amount')->nullable();
            $table->text('collected_amount')->nullable();
            $table->text('start_date')->nullable();
            $table->text('members_contributed')->nullable();
            $table->text('end_date')->nullable();
            $table->text('photo')->nullable();
            $table->string('status')->nullable();
            $table->text('detail')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contributions');
    }
}
