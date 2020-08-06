<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('cookie');
            $table->string('name', 255)->default('');
            $table->integer('frequency')->default(2);
            $table->string('proxy', 191)->default('');
            $table->boolean('is_valid')->default(true);
            $table->integer('next_run_time')->default(0);
            $table->text('error_log')->nullable();
            $table->string('sticker_collection_id', 191)->nullable();
            $table->boolean('comment_on')->default(false);
            $table->text('comment_content');
            $table->integer('start_time')->default(8);
            $table->integer('end_time')->default(23);
            $table->integer('reaction_type')->default(1);
            $table->string('bot_target', 191)->default('all');
            $table->text('black_list');
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
        Schema::dropIfExists('bots');
    }
}
