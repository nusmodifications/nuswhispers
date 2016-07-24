<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfessionCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('confession_categories', function(Blueprint $table)
		{
			$table->integer('confession_category_id')->unsigned();
			$table->integer('confession_id')->unsigned();
			$table->primary(array('confession_category_id', 'confession_id'), 'confession_categories_primary');
			$table->foreign('confession_category_id')->references('confession_category_id')->on('categories')->onDelete('cascade')->onUpdate('cascade');
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
		Schema::drop('confession_categories');
	}

}
