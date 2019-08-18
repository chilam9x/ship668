<?php

use Illuminate\Database\Seeder;

class UpdateDistrictTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $id1 = [552, 561, 562, 563, 558, 560, 557, 556];
        $id2 = [555, 553, 554, 569, 559, 564, 565, 566, 567, 568, 570];
        $id3 = [572, 573, 574, 571];
        App\Models\District::whereIn('id', $id1)->update(['district_type'=> 1]);
        App\Models\District::whereIn('id', $id2)->update(['district_type'=> 2]);
        App\Models\District::whereIn('id', $id3)->update(['district_type'=> 3]);
    }
}
