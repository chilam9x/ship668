<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttributeNewCustomerIntoBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->integer('is_customer_new')->default(0)->comment('[0] không phải khách hàng mới; [1] khách hàng mới');
            $table->integer('agency_confirm_id')->nullable()->comment('Đại lý xác nhận đơn hàng lấy về ok');
            $table->integer('agency_confirm_date')->nullable()->comment('Thời gian đại lý xác nhận đơn hàng lấy về ok');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('is_customer_new');
            $table->dropColumn('agency_confirm_id');
            $table->dropColumn('agency_confirm_date');
        });
    }
}
