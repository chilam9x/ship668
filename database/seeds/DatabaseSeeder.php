<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(ProvinceDistrictWardTableSeeder::class);
        $this->call(DistrictTypeSeeder::class);
        $this->call(InterMunicipalUPSeeder::class);
        $this->call(ProvincialUPSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(SpeciallUPSeeder::class);
        $this->call(SpecialPriceSeeder::class);
        $this->call(UpdateDistrictTypeSeeder::class);
        $this->call(VersionSeeder::class);
    }
}
