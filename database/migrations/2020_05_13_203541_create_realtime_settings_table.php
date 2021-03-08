<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRealtimeSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('realtime_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('stock_id');
            $table->float('upperlimit')->nullable();  //上限値
            $table->dateTime('upperlimit_settingat')->nullable();
            $table->float('lowerlimit')->nullable();  //下限値
            $table->dateTime('lowerlimit_settingat')->nullable();
            $table->float('changerate')->nullable();    //変化率
            $table->dateTime('changerate_settingat')->nullable();
            $table->boolean('ismatched_upperlimit');
            $table->boolean('ismatched_lowerlimit');
            $table->boolean('ismatched_changerate');
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
        Schema::dropIfExists('realtime_settings');
    }
}
