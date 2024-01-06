<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news_posts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('title')->nullable();
            $table->foreignIdFor(User::class, 'created_by_id')->nullable();
            $table->longText('details')->nullable();
            $table->text('photo')->nullable();
            $table->string('category')->nullable();
            $table->string('views_count')->nullable();
            $table->string('job_nature')->nullable();
            $table->string('job_minimum_academic_qualification')->nullable();
            $table->string('job_required_expirience')->nullable();
            $table->string('job_how_to_apply')->nullable();
            $table->string('job_phone_number')->nullable();
            $table->string('job_location')->nullable();
            $table->string('job_deadline')->nullable();
            $table->string('job_slots')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news_posts');
    }
}
