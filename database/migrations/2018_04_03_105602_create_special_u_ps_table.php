<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpecialUPsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('special_u_ps', function (Blueprint $table) {
            $table->increments('id');
            $table->double('weight')->nullable();
            $table->double('km')->nullable();
            $table->integer('province_from')->nullable();
            $table->integer('province_to')->nullable();
            $table->double('price')->nullable();
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
        Schema::dropIfExists('special_u_ps');
    }
}
