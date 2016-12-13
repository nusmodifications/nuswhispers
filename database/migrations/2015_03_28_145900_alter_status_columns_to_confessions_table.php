<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterStatusColumnsToConfessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('confessions', function (Blueprint $table) {
            $table->dropColumn(['created_on', 'status_last_updated_on']);

            $table->timestamps();
            $table->timestamp('status_updated_at');
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
            $table->dropTimestamps();
            $table->dropColumn('status_updated_at');

            $table->timestamp('created_on')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('status_last_updated_on')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }
}
