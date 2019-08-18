<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::where(['email' => 'admin@example.com'])->first();
	    if (empty($admin)) {
	      	User::insert([[
		        'username' => 'Admin',
		        'birth_day' => '1996/08/19',
		        'name' => 'Admin',
		        'email' => 'admin@example.com',
		        'password' => bcrypt('admin'),
		        'role' => 'admin',
		        'status' => 'active',
		        'province_id' => '1',
		        'district_id' => '1',
		        'ward_id' => '1',
		        'home_number' => '125A',
		        'phone_number' => '0999999999',
	      	]]);
	    }
    }
}
