<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaidHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paid_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id')->nullable();
            $table->integer('user_create')->nullable();
            $table->tinyInteger('type')->default('0')->comment("0 là đại lý thanh toán cho hệ thống, 1 là hệ thống thanh toán cho đại lý");
            $table->double('value')->default('0');
            $table->tinyInteger('status')->default('0')->comment("0 = chưa xác nhận, 1 là đã xác nhận");
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
        Schema::dropIfExists('paid_histories');
    }
}
