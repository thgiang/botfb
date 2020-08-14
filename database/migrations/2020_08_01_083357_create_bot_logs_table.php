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
            $table->bigInteger('bot_id');
            $table->string('bot_fid', 191);
            $table->string('post_id', 191);
            $table->string('action', 191)->default('COMMENT');
            $table->string('comment_id', 191)->nullable();
            $table->string('sticker_id', 191)->nullable();
            $table->text('comment_content')->nullable();
            $table->timestamps();

            $table->index('bot_id', 'IDX_BOT_ID');
            $table->index('bot_fid', 'IDX_BOT_FID');
            $table->index('post_id', 'IDX_POST_ID');
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
