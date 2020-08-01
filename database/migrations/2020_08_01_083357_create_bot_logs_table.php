<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bot_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bot_id')->index('INDEX_BOT_ID');
            $table->string('post_id', 255);
            $table->string('comment_id', 255)->nullable();
            $table->string('action')->default('COMMENT');
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
        Schema::dropIfExists('bot_logs');
    }
}
