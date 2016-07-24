<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfessionTagsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('confession_tags', function(Blueprint $table)
		{
			$table->integer('confession_tag_id')->unsigned();
			$table->integer('confession_id')->unsigned();
			$table->primary(array('confession_tag_id', 'confession_id'));
			$table->foreign('confession_tag_id')->references('confession_tag_id')->on('tags')->onDelete('cascade')->onUpdate('cascade');
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
		Schema::dropIfExists('confession_tags');
	}

}
