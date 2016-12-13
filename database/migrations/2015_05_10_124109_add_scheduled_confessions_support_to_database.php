<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddScheduledConfessionsSupportToDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modify confession status enum to support scheduled
        DB::statement("ALTER TABLE confessions CHANGE COLUMN status status ENUM('Pending', 'Scheduled', 'Approved', 'Featured', 'Rejected') DEFAULT 'Pending'");
        DB::statement("ALTER TABLE confession_logs CHANGE COLUMN status_before status_before ENUM('Pending', 'Scheduled', 'Approved', 'Featured', 'Rejected')");
        DB::statement("ALTER TABLE confession_logs CHANGE COLUMN status_after status_after ENUM('Pending', 'Scheduled', 'Approved', 'Featured', 'Rejected')");

        Schema::create('confession_queue', function (Blueprint $table) {
            $table->increments('confession_queue_id')->unsigned();
            $table->integer('confession_id')->unsigned();
            $table->enum('status_after', ['Approved', 'Featured', 'Rejected']);
            $table->timestamp('update_status_at');
            $table->foreign('confession_id')->references('confession_id')->on('confessions')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE confessions CHANGE COLUMN status status ENUM('Pending', 'Approved', 'Featured', 'Rejected') DEFAULT 'Pending'");
        DB::statement("ALTER TABLE confession_logs CHANGE COLUMN status_before status_before ENUM('Pending', 'Approved', 'Featured', 'Rejected')");
        DB::statement("ALTER TABLE confession_logs CHANGE COLUMN status_after status_after ENUM('Pending', 'Approved', 'Featured', 'Rejected')");

        Schema::drop('confession_queue');
    }
}
