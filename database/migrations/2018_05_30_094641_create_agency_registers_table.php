<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgencyRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agency_registers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('col_id')->unsigned();
            $table->string('name')->nullable();
            $table->string('phone_number')->unique()->nullable();
            $table->integer('province_id')->nullable();
            $table->integer('district_id')->nullable();
            $table->integer('ward_id')->nullable();
            $table->string('home_number')->nullable();
            $table->timestamps();
            $table->foreign('col_id')
                ->references('id')->on('collaborator_registers')
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
        Schema::dropIfExists('agency_registers');
    }
}
