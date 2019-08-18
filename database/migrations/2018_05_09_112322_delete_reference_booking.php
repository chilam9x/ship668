<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteReferenceBooking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('book_deliveries', function (Blueprint $table) {
            $table->foreign('book_id')
                ->references('id')->on('bookings')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('book_deliveries', function (Blueprint $table) {
            $table->dropForeign('book_deliveries_book_id_foreign');
        });
    }
}
