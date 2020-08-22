<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemProxyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_proxies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('proxy');
            $table->string('is_live')->default(true);
            $table->string('bot_id')->default(false);
            $table->integer('expired')->nullable();
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
        Schema::dropIfExists('system_proxies');
    }
}
