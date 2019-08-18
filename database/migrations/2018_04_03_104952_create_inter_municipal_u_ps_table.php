<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInterMunicipalUPsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inter_municipal_u_ps', function (Blueprint $table) {
            $table->increments('id');
            $table->double('weight')->nullable();
            $table->double('km')->nullable();
            $table->double('price')->nullable();
            $table->integer('district_type')->nullable();
            $table->timestamps();
            $table->index('district_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inter_municipal_u_ps');
    }
}
