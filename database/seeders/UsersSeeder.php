<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
            'username' => 'Admin',
            'email' => 'administrator@gmail.com',
            'password' => bcrypt('password')
        ]);

        DB::table('users')->insert([
            'name' => 'User',
            'username' => 'User',
            'email' => 'user@gmail.com',
            'password' => bcrypt('password')
        ]);
    }
}
