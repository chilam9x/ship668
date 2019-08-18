<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id');
            $table->double('price')->default(0);
            $table->string('withdrawal_type')->nullable()->comment('[transfer] chuyển khoản; [cash] tiền mặt; hình thức rút tiền');
            $table->integer('payment_status')->default(0)->comment('[0] chưa thanh toán; [1] đã thanh toán; trạng thái thanh toán tiền cho khách');
            $table->datetime('payment_date')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone_number')->nullable();
            $table->string('payment_code')->nullable();
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
        Schema::dropIfExists('wallets');
    }
}
