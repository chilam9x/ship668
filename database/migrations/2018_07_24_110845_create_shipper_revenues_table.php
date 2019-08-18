<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShipperRevenuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipper_revenues', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shipper_id')->unsigned();
            $table->double('total_price')->default('0');
            $table->double('total_COD')->default('0');
            $table->double('price_paid')->default('0');
            $table->double('COD_paid')->default('0');
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
        Schema::dropIfExists('shipper_revenues');
    }
}
