<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class CategoriesSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('categories')->delete();

		DB::table('categories')->insert(array(
			array('confession_category' => 'Funny'),
			array('confession_category' => 'Lost and Found'),
			array('confession_category' => 'Romance'),
			array('confession_category' => 'Rant')
		));
	}

}
