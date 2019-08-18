<?php

use Illuminate\Database\Seeder;
use App\Models\DistrictType;

class DistrictTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DistrictType::insert([
            [
                'name' => 'Nội thành'
            ], [
                'name' => 'Ngoại thành 1'
            ], [
                'name' => 'Ngoại thành 2',
            ], [
                'name' => 'Trung tâm tỉnh/TP',
            ], [
                'name' => 'Huyện Xã',
            ],
        ]);
    }
}
