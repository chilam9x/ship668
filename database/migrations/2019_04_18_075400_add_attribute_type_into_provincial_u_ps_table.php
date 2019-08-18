<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttributeTypeIntoProvincialUPsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provincial_u_ps', function (Blueprint $table) {
            $table->integer('type')->default(0)->comment('[0] bảng giá thường, [1] bảng giá vip, [2] bảng giá pro');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provincial_u_ps', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
