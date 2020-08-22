<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Add2ColsToWhiteGroupIdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('white_group_ids', function (Blueprint $table) {
            $table->integer('latest_post_time')->comment("T.Gian bài mới nhất trên FB")->default(0);
            $table->string('latest_post_fid')->comment("ID bài mới nhất trên FB")->default('');
            $table->integer('last_run_time')->comment("Lần tương tác gần nhất")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('white_group_ids', function (Blueprint $table) {
            //
        });
    }
}
