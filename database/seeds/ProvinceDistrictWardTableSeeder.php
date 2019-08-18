<?php

use Illuminate\Database\Seeder;
use App\Models\Province;
use App\Models\District;
use App\Models\Ward;

class ProvinceDistrictWardTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file = database_path('data/vietnam.province.district.ward.csv');

	    if (is_file($file) && $opened_file = fopen($file, 'r')) {
	      	/*Ward::truncate();
	      	District::truncate();
      		Province::truncate();*/

	      	$x = 0;
	      	while ($csv = fgetcsv($opened_file)) {

		        if ( $x > 0) {

		        	$provinceSlug = str_slug($csv[0]);
		        	$province = Province::where(['name_slug' => $provinceSlug])->first();
		          	if (empty($province)) {
			            $province = Province::create([
			            	'name' => $csv[0],
			              	'name_slug' => $provinceSlug,
			              	'province_type' => 0,
			            ]);
		          	}

		        	$districtSlug = str_slug($csv[2]);
		          	$district = District::where(['provinceId' => $province->id, 'name_slug' => $districtSlug])->first();
		          	if (empty($district)) {
			            $district = District::create([
			            	'provinceId' => $province->id,
			              	'name' => $csv[2],
			              	'name_slug' => $districtSlug,
			            ]);
		          	}

		          	Ward::create([
	          			'provinceId' => $province->id,
			            'districtId' => $district->id,
			            'name' => $csv[4],
			            'name_slug' => str_slug($csv[4]),
		          	]);
		        }
		        ++$x;

	      	}

	      	echo "\nimporeted " . $x . " provinces & districts & wards";
	    }
    }
}
