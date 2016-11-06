<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterFacebookPostIdColumnToConfessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('confessions', function (Blueprint $table) {
            $table->dropColumn('fb_post_id');
        });

        Schema::table('confessions', function (Blueprint $table) {
            $table->string('fb_post_id', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('confessions', function (Blueprint $table) {
            $table->dropColumn('fb_post_id');
        });

        Schema::table('confessions', function (Blueprint $table) {
            $table->bigInteger('fb_post_id')->nullable();
        });
    }
}
