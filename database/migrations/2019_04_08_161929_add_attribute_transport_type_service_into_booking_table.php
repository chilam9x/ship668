<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttributeTransportTypeServiceIntoBookingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->integer('transport_type_service1')->default(0)->comment('[1]: Giao hẹn giờ (phương sai 30 phút); [0]: không sử dụng dịch vụ thêm');
            $table->integer('transport_type_service2')->default(0)->comment('[1]: Giao 1 phần, trả lại 1 phần; [0]: không sử dụng dịch vụ thêm');
            $table->integer('transport_type_service3')->default(0)->comment('[1]: Giao bến xe, nhà xe; [0]: không sử dụng dịch vụ thêm');
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
            $table->dropColumn('transport_type_service1');
            $table->dropColumn('transport_type_service2');
            $table->dropColumn('transport_type_service3');
        });
    }
}
