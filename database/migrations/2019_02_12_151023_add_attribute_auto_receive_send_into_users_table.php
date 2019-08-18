<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttributeAutoReceiveSendIntoUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('auto_receive')->default(0)->comment('[0] không cho phép; [1] cho phép thấy đơn hàng đi lấy');
            $table->integer('auto_send')->default(0)->comment('[0] không cho phép; [1] cho phép thấy đơn hàng đi giao');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('auto_receive');
            $table->dropColumn('auto_send');
        });
    }
}
