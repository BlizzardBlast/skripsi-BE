<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'administrator@gmail.com',
            'role' => 'admin',
            'password' => bcrypt('Password123')
        ]);

        DB::table('users')->insert([
            'name' => 'User',
            'username' => 'user',
            'email' => 'user@gmail.com',
            'role' => 'member',
            'password' => bcrypt('Password321')
        ]);
    }
}
