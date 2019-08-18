<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid')->nullable();
            $table->string('avatar')->nullable();
            $table->string('username')->unique()->nullable();
            $table->date('birth_day')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->integer('province_id')->nullable();
            $table->integer('district_id')->nullable();
            $table->integer('ward_id')->nullable();
            $table->string('home_number')->nullable();
            $table->string('phone_number')->unique()->nullable();
            $table->string('id_number')->nullable();
            $table->enum('role', ['admin', 'shipper', 'customer', 'collaborators', 'partner'])->default('customer');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('bank_account')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('fb_id')->nullable();
            $table->string('gg_id')->nullable();
            $table->double('total_COD')->default('0');
            $table->tinyInteger('delete_status')->default('0');
            $table->rememberToken();
            $table->timestamps();
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
