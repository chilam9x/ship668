<?php

use Illuminate\Database\Seeder;

class VersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Version::insert([
            [
                'version_code' => '1',
                'version_name' => '1.0',
                'description' => 'Version hiện tại',
                'force_upgrade' => true,
                'category' => 'customer',
                'device_type' => 'android'
            ],[
                'version_code' => '1',
                'version_name' => '1.0',
                'description' => 'Version hiện tại',
                'force_upgrade' => true,
                'category' => 'shipper',
                'device_type' => 'android'
            ], [
                'version_code' => '1',
                'version_name' => '1.0',
                'description' => 'Version hiện tại',
                'force_upgrade' => true,
                'category' => 'customer',
                'device_type' => 'ios',
            ], [
                'version_code' => '1',
                'version_name' => '1.0',
                'description' => 'Version hiện tại',
                'force_upgrade' => true,
                'category' => 'shipper',
                'device_type' => 'ios',
            ]
        ]);
    }
}
