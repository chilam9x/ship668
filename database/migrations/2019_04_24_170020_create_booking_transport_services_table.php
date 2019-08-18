<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingTransportServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('booking_transport_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key');
            $table->double('price');
            $table->integer('status')->default(1);
            $table->integer('book_id')->unsigned();
            $table->integer('service_id')->unsigned();
            $table->timestamps();
            $table->integer('user_id')->unsigned();
            $table->index('user_id');
            $table->index('book_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobs');
    }
}
