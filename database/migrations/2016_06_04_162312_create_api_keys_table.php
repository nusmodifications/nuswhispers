<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->increments('api_key_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('key')->unique();
            $table->timestamp('last_used_on')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('created_on')->default(DB::raw('CURRENT_TIMESTAMP'));
            
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
        Schema::dropIfExists('api_keys');
    }
}
