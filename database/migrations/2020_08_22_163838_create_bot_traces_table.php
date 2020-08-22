<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotTracesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bot_traces', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('success')->default(true);
            $table->bigInteger('bot_id');
            $table->string('code');
            $table->string('bot_facebook_uid');
            $table->string('content');
            $table->text('data')->nullable();
            $table->timestamps();

            $table->index('code', 'IDX_CODE');
            $table->index('bot_id', 'IDX_BOT_ID');
            $table->index('bot_facebook_uid', 'IDX_BOT_FACEBOOK_UID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bot_traces');
    }
}
