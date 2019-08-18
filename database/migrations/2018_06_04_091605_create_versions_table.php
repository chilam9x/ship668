<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('versions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('version_code')->nullable();
            $table->string('version_name')->nullable();
            $table->string('description')->nullable();
            $table->boolean('force_upgrade')->nullable();
            $table->enum('category',['shipper', 'customer'])->default('customer');
            $table->enum('device_type',['ios', 'android'])->default('android');
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
        Schema::dropIfExists('versions');
    }
}
