<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use App\Models\Confession;

class ConfessionsDemoSeeder extends Seeder {

    const RANDOM_COUNT = 100;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('confessions')->delete();

        for ($i = 0; $i < self::RANDOM_COUNT; $i++)
        {
            Confession::create([
                'content' => "This is pending confession #$i. I want to use Eloquent's active record building to build a search query, but it is going to be a LIKE search. I have found the User::find(\$term) or User::find(1), but this is not generating a like statement. I'm not looking for a direct answer, but if someone could at least give me a direction to look in that'd be great!",
                'status' => 'Pending'
            ]);
        }

    }

}
