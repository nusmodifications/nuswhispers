<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class UsersSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'email' => 'zy@zy.sg',
                'password' => Hash::make('admin'),
                'role' => 'Administrator'
            ],
            [
                'email' => 'a0088278@nus.edu.sg',
                'password' => Hash::make('genius'),
                'role' => 'Administrator'
            ]
        ]);
    }

}
