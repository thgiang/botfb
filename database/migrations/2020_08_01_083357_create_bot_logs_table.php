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
            $table->string('post_id', 191);
            $table->string('action', 191)->default('COMMENT');
            $table->string('comment_id', 191)->nullable();
            $table->string('sticker_id', 191)->nullable();
            $table->text('comment_content');
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
