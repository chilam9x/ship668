<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttributeWeightPricePlusIntoProvincialUPsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provincial_u_ps', function (Blueprint $table) {
            $table->double('weight_plus')->default(1000)->comment('Số gram tăng lên tính tiền thêm tương ứng');
            $table->double('price_plus')->default(3000)->comment('Số tiền cộng thêm mỗi gram tăng lên. Ví dụ: 1000 gram tăng thêm 3000 VND');
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
            $table->dropColumn('weight_plus');
            $table->dropColumn('price_plus');
        });
    }
}
