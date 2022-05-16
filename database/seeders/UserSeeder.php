<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::create(array(
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'username'  => 'admin',
            'email' => "info@jaadara.com",
            'address' => "Egypt, Elmansoura",
            'password' => bcrypt("password"),
            'phone' => '9665214578952',
            'email_verified_at' => now()
        ));

        $admin->assignRole('admin');
    }
}
