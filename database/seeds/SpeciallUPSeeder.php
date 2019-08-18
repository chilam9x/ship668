<?php

use Illuminate\Database\Seeder;

class SpeciallUPSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            50 => [
                32 => [0 => 11385, 50 => 16445, 100 => 27198, 250 => 35420, 500 => 51799, 1000 => 66792, 1500 => 81087]
            ],
            1 => [
                50 => [0 => 11500, 50 => 16825, 100 => 27830, 250 => 36129, 500 => 52877, 1000 => 68184, 1500 => 82858]
            ],
            32 => [
                1 => [0 => 11385, 50 => 16445, 100 => 27198, 250 => 35420, 500 => 51799, 1000 => 66792, 1500 => 81087]
            ],
        ];

        foreach ($data as $from => $value){
            foreach ($value as $to => $v){
                foreach ($v as $weight => $price){
                    App\Models\SpecialUP::insert([
                        'weight' => $weight,
                        'price' => $price,
                        'province_from' => $from,
                        'province_to' => $to
                    ]);
                }
            }
        }
    }
}
