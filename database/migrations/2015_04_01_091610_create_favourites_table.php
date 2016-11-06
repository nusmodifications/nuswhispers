<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFavouritesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('favourites', function (Blueprint $table) {
            $table->integer('confession_id')->unsigned();
            $table->bigInteger('fb_user_id');
            $table->primary(['confession_id', 'fb_user_id']);
            $table->foreign('confession_id')->references('confession_id')->on('confessions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('fb_user_id')->references('fb_user_id')->on('fb_users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('favourites');
    }
}
