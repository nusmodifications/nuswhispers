<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->increments('profile_id')->unsigned();

            $table->integer('user_id')->unsigned();
            $table->string('provider_name', 255);
            $table->unique(['user_id', 'provider_name']);
            $table->string('provider_id', 255);
            $table->text('provider_token')->nullable();
            $table->text('page_token')->nullable();
            $table->text('data')->nullable();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_profiles');
    }
}
