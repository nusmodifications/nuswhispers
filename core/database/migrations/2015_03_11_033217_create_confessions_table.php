<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateConfessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('confessions', function (Blueprint $table) {
            $table->increments('confession_id')->unsigned();
            $table->text('content');
            $table->text('images')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Featured', 'Rejected'])->default('Pending');
            $table->bigInteger('fb_post_id')->nullable();
            $table->timestamp('created_on')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('views')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('confessions');
    }
}
