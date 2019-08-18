<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->char('uuid', 36)->nullable();
            $table->integer('first_agency')->nullable();
            $table->integer('last_agency')->nullable();
            $table->integer('current_agency')->nullable();
            $table->integer('sender_id')->nullable();
            $table->integer('receiver_id')->nullable();
            $table->integer('send_province_id');
            $table->integer('send_district_id');
            $table->integer('send_ward_id');
            $table->string('send_homenumber')->nullable();
            $table->string('send_full_address');
            $table->string('send_name');
            $table->string('send_phone');
            $table->double('send_lat')->nullable();
            $table->double('send_lng')->nullable();
            $table->integer('receive_province_id');
            $table->integer('receive_district_id');
            $table->integer('receive_ward_id');
            $table->string('receive_homenumber')->nullable();
            $table->string('receive_full_address');
            $table->string('receive_name');
            $table->string('receive_phone');
            $table->double('receive_lat')->nullable();
            $table->double('receive_lng')->nullable();
            $table->double('weight');
            $table->double('price')->nullable();
            $table->double('incurred')->default('0');
            $table->double('paid')->default('0');
            $table->float('length')->nullable();
            $table->float('width')->nullable();
            $table->float('height')->nullable();
            $table->double('COD')->default('0');
            $table->tinyInteger('transport_type');
            $table->tinyInteger('receive_type');
            $table->tinyInteger('payment_type');
            $table->text('other_note')->nullable();
            $table->enum('status', ['new', 'taking', 'sending', 'completed', 'return', 'cancel', 'move'])->default('new');
            $table->enum('sub_status', ['none', 'delay', 'deny', 'move_return'])->default('none');
            $table->enum('COD_status', ['pending', 'finish'])->default('pending');
            $table->dateTime('payment_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->index('uuid');
            $table->index('sender_id');
            $table->index('receiver_id');
            $table->index('send_ward_id');
            $table->index('status');
            $table->index('sub_status');
            $table->index('created_at');
            $table->index('current_agency');
            $table->index('first_agency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
}
