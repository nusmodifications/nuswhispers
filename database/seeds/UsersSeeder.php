<?php

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'email' => 'test@nuswhispers.local',
                'password' => Hash::make('admin'),
                'role' => 'Administrator',
            ],
        ]);
    }
}
