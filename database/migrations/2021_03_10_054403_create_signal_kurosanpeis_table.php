<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSignalKurosanpeisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('signal_kurosanpeis', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('stock_id');
            $table->float('deltaprice');
            $table->float('deltarate');
            $table->float('minus1price');
            $table->float('minus2price');
            $table->float('minus3price');
            $table->date('baseday');
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
        Schema::dropIfExists('signal_kurosanpeis');
    }
}
