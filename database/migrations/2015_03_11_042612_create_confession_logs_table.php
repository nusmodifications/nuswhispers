<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateConfessionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('confession_logs', function (Blueprint $table) {
            $table->increments('confession_log_id')->unsigned();
            $table->integer('confession_id')->unsigned();
            $table->integer('changed_by_user')->unsigned();
            $table->enum('status_before', ['Pending', 'Approved', 'Featured', 'Rejected']);
            $table->enum('status_after', ['Pending', 'Approved', 'Featured', 'Rejected']);
            $table->timestamp('created_on')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('confession_id')->references('confession_id')->on('confessions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('changed_by_user')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('confession_logs');
    }
}
