<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('products')->insert([
            'name' => 'AkopiA',
            'type' => 'Arabica',
            'price' => '5000',
            'description' => 'dgfr'

        ]);

        DB::table('products')->insert([
            'name' => 'BkopiB',
            'type' => 'Robusta',
            'price' => '7000',
            'description' => 'dgfr'

        ]);

        DB::table('products')->insert([
            'name' => 'CkopiC',
            'type' => 'Liberica',
            'price' => '9000',
            'description' => 'dgfr'

        ]);
    }
}
