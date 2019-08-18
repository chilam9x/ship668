<?php

use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            0 => ['type' => 'customer', 'key' => 'receive_type', 'name' => 'Nhận hàng tại bưu cục', 'value' => 7],
            1 => ['type' => 'customer', 'key' => 'transport_type', 'name' => 'Giao hàng nhanh', 'value' => 5],
            2 => ['type' => 'customer', 'key' => 'cod', 'name' => 'COD', 'value' => 0],
            3 => ['type' => 'shipper', 'key' => 'ship', 'name' => 'shipper', 'value' => 4],
            4 => ['type' => 'partner', 'key' => 'partner', 'name' => 'Đối tác', 'value' => 0],
        ];
        foreach ($data as $type => $value) {
            \App\Models\Setting::insert([$value]);
        }
    }
}
