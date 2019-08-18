<?php

use Illuminate\Database\Seeder;
use App\Models\District;
use App\Models\Ward;

class DistrictWardTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file = database_path('data/list-of-wards.csv');

	    if (is_file($file) && $opened_file = fopen($file, 'r')) {
	      	Ward::truncate();
	      	District::truncate();


	      	$x = 0;
	      	while ($csv = fgetcsv($opened_file)) {

		        if ( $x > 0) {

		        	$districtSlug = str_slug($csv[1]);

		          	$district = District::where(['name_slug' => $districtSlug])->first();
		          	if (empty($district)) {
			            $district = District::create([
			              	'name' => $csv[1],
			              	'name_slug' => $districtSlug,
			            ]);
		          	}

		          	Ward::create([
			            'districtId' => $district->id,
			            'name' => $csv[2],
			            'name_slug' => str_slug($csv[2]),
		          	]);
		        }
		        ++$x;

	      	}

	      	echo "\nimporeted " . $x . " districts & wards";
	    }
    }
}
