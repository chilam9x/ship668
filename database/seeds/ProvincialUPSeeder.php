<?php

use Illuminate\Database\Seeder;
use \App\Models\ProvincialUP;

class ProvincialUPSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            4 => [0 => 10120, 50 => 10120, 100 => 12650, 250 => 15813, 500 => 20240, 1000 => 24035, 1500 => 26565],
            5 => [0 => 10120, 50 => 10120, 100 => 12650, 250 => 15813, 500 => 20240, 1000 => 24035, 1500 => 26565],
        ];

        ProvincialUP::insert([
            [
                'weight' => 0,
                'price' => 19000,
                'district_type' => 1
            ],
            [
                'weight' => 0,
                'price' => 24000,
                'district_type' => 2
            ],
            [
                'weight' => 0,
                'price' => 34000,
                'district_type' => 3
            ]
        ]);
        foreach ($data as $k => $v){
            foreach ($v as $k1=>$v1)
            ProvincialUP::insert([
                'weight' => $k1,
                'price' => $v1,
                'district_type' => $k
            ]);
        }
    }
}
