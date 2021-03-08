<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRealtimeCheckingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('realtime_checkings', function (Blueprint $table) {
            $table->id();
            $table->integer('realtime_setting_id');
            $table->float('price')->nullable();   //現在値
            $table->dateTime('price_checkingat')->nullable();   //現在値
            $table->float('pre_price')->nullable();   //前回(1分前)現在値
            $table->dateTime('pre_price_checkingat')->nullable();   //前回(1分前)現在値
            $table->float('rate')->nullable();   //前回(1分前)現在値
            $table->dateTime('rate_checkingat')->nullable();   //前回(1分前)現在値
            $table->float('pre_rate')->nullable();   //前回(1分前)現在値
            $table->dateTime('pre_rate_checkingat')->nullable();   //前回(1分前)現在値
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
        Schema::dropIfExists('realtime_checkings');
    }
}
