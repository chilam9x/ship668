<?php

use Illuminate\Database\Seeder;
use App\Models\InterMunicipalUP;

class InterMunicipalUPSeeder extends Seeder
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
                0 => [0 => 10120, 50 => 14927, 100 => 20873, 250 => 30234, 500 => 41993, 1000 => 50600, 1500 => 61226],
                100 => [0 => 10753, 50 => 15813, 100 => 23023, 250 => 32055, 500 => 43010, 1000 => 52877, 1500 => 65401],
                300 => [0 => 12650, 50 => 17710, 100 => 29095, 250 => 37824, 500 => 55281, 1000 => 71346, 1500 => 86653],
            ],
            5 => [
                0 => [0 => 11880, 50 => 17532, 100 => 24503, 250 => 35492, 500 => 49902, 1000 => 59400, 1500 => 71874],
                100 => [0 => 12623, 50 => 18563, 100 => 27027, 250 => 37571, 500 => 50490, 1000 => 62073, 1500 => 76775],
                300 => [0 => 14850, 50 => 20790, 100 => 34155, 250 => 44420, 500 => 64896, 1000 => 83754, 1500 => 101723],
            ],
        ];
        foreach ($data as $type => $value){
            foreach ($value as $km => $v){
                foreach ($v as $weight => $price){
                    InterMunicipalUP::insert([
                        'weight' => $weight,
                        'km' => $km,
                        'price' => $price,
                        'district_type' => $type
                    ]);
                }
            }
        }
    }
}
