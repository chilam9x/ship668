<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnOweToBookings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->tinyInteger('owe')->default('0')->comment('0 = nợ, 1 = đã thanh toán');
            $table->dateTime('completed_at')->nullable();
        });
        Schema::table('book_deliveries', function (Blueprint $table) {
            $table->dateTime('completed_at')->nullable();
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
            $table->dropColumn('owe');
            $table->dropColumn('completed_at');
        });
        Schema::table('book_deliveries', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
}
