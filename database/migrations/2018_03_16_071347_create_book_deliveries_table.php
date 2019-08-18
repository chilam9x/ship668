<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('book_deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('book_id')->unsigned();
            $table->string('send_address')->nullable();;
            $table->double('send_lat')->default('0');
            $table->double('send_lng')->default('0');
            $table->string('receive_address')->nullable();
            $table->double('receive_lat')->default('0');
            $table->double('receive_lng')->default('0');
            $table->text('note')->nullable();
            $table->enum('status', ['processing', 'delay', 'in_warehouse', 'completed', 'return', 'deny', 'cancel'])->default('processing');
            $table->enum('category', ['receive', 'send', 'return', 'receive-and-send', 'move'])->default('receive');
            $table->integer('delay_total')->default('0');
            $table->integer('current_agency')->nullable();
            $table->integer('last_agency')->nullable();
            $table->tinyInteger('sending_active')->default('0');
            $table->tinyInteger('last_move')->default('0');
            $table->timestamps();
            $table->index('user_id');
            $table->index('book_id');
            $table->index('category');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('book_deliveries');
    }
}
