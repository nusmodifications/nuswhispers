<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RevampUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->dropColumn(['username', 'password']);
        });

        Schema::table('users', function(Blueprint $table)
        {
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password', 60);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->dropColumn(['name', 'email', 'password', 'created_at', 'updated_at', 'remember_token']);
        });

        Schema::table('users', function(Blueprint $table) {
            $table->string('username', 255)->unique();
            $table->string('password', 255);
        });
    }

}
