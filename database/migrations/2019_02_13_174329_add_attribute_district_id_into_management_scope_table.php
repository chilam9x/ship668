<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttributeDistrictIdIntoManagementScopeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('management_ward_scopes', function (Blueprint $table) {
            $table->integer('district_id')->nullable()->comment();
            $table->integer('province_id')->nullable()->comment();
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
            $table->dropColumn('district_id');
            $table->dropColumn('province_id');
        });
    }
}
