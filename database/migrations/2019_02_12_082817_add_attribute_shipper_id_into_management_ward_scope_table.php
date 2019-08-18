<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttributeShipperIdIntoManagementWardScopeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('management_ward_scopes', function (Blueprint $table) {
            $table->integer('shipper_id')->nullable()->comment();
            $table->dropForeign(['agency_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('management_ward_scopes', function (Blueprint $table) {
            $table->dropColumn('shipper_id');
        });
    }
}
