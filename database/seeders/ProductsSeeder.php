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
            'subname' => 'Canis Alvares',
            'origin' => 'Sumatra',
            'characteristic' => 'Fruity Acid',
            'type' => 'Arabica',
            'price' => '5000',
            'description' => 'dgfr'

        ]);

        DB::table('products')->insert([
            'name' => 'BkopiB',
            'subname' => 'Canis Betares',
            'origin' => 'Sulawesi',
            'characteristic' => 'Fruity Acid',
            'type' => 'Robusta',
            'price' => '7000',
            'description' => 'dgfr'

        ]);

        DB::table('products')->insert([
            'name' => 'CkopiC',
            'subname' => 'Canis Charles',
            'origin' => 'Papua',
            'characteristic' => 'Fruity Acid',
            'type' => 'Liberica',
            'price' => '9000',
            'description' => 'dgfr'

        ]);
    }
}
