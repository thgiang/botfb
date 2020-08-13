<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhiteGroupIdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('white_list_ids', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('bot_id');
            $table->string('fb_id');
            $table->timestamps();

            $table->index('bot_id', 'IDX_BOT_ID');
            $table->index('fb_id', 'IDX_FB_ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('white_list_ids');
    }
}
