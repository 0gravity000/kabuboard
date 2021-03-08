<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->integer('code')->unique();
            $table->string('name');
            $table->integer('market_id');
            $table->integer('industry_id');
            //minitly check
            $table->float('price')->nullable();
            $table->float('rate')->nullable();
            //daily check
            $table->float('pre_end_price')->nullable();
            $table->float('start_price')->nullable();
            $table->float('end_price')->nullable();
            $table->float('highest_price')->nullable();
            $table->float('lowest_price')->nullable();
            $table->bigInteger('volume')->nullable();
            
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
        Schema::dropIfExists('stocks');
    }
}
