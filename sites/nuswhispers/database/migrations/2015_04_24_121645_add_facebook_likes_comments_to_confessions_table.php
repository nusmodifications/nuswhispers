<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFacebookLikesCommentsToConfessionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('confessions', function(Blueprint $table)
		{
			$table->integer('fb_like_count')->unsigned()->default(0);
			$table->integer('fb_comment_count')->unsigned()->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('confessions', function(Blueprint $table)
		{
			$table->dropColumn('fb_like_count');
			$table->dropColumn('fb_comment_count');
		});
	}

}
