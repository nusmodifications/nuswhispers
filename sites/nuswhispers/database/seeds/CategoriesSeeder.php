<?php

use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->delete();

        DB::table('categories')->insert([
            ['confession_category' => 'Funny'],
            ['confession_category' => 'Lost and Found'],
            ['confession_category' => 'Romance'],
            ['confession_category' => 'Rant'],
            ['confession_category' => 'Nostalgia'],
            ['confession_category' => 'Advice']
        ]);
    }
}
