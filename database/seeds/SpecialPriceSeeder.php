<?php

use Illuminate\Database\Seeder;

class SpecialPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            4 => [
                0 => 4428,
                100 => 5440,
                300 => 10753,
            ],
            5 => [
                0 => 5198,
                100 => 6386,
                300 => 12623,
            ],
        ];
        \App\Models\SpecialPrice::insert([
            [
                'district_type' => 1,
                'price' => 2151,
            ]
        ]);
        \App\Models\SpecialPrice::insert([
            [
                'province_from' => 50,
                'province_to' => 32,
                'price' => 8982,
            ],
            [
                'province_from' => 50,
                'province_to' => 1,
                'price' => 10247,
            ],
            [
                'province_from' => 1,
                'province_to' => 32,
                'price' => 8982,
            ]
        ]);
        foreach ($data as $type => $v){
            foreach ($v as $km=>$price)
                \App\Models\SpecialPrice::insert([
                    'district_type' => $type,
                    'km' => $km,
                    'price' => $price,
                ]);
        }
    }
}
