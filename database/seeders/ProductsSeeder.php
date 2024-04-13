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
            'description' => 'dgfr',

            'acidity' => 'Sour',
            'mouthfeel' => 'Light',
            'Sweetness' => 'Sweet'
        ]);

        DB::table('products')->insert([
            'name' => 'BkopiB',
            'subname' => 'Canis Betares',
            'origin' => 'Sulawesi',
            'characteristic' => 'Bitter Roasred',
            'type' => 'Robusta',
            'price' => '7000',
            'description' => 'dgfr',

            'acidity' => 'Neutral',
            'mouthfeel' => 'Heavy',
            'Sweetness' => 'Bitter'
        ]);

        DB::table('products')->insert([
            'name' => 'CkopiC',
            'subname' => 'Canis Charles',
            'origin' => 'Papua',
            'characteristic' => 'Earthy',
            'type' => 'Arabica',
            'price' => '9000',
            'description' => 'dgfr',

            'acidity' => 'Neutral',
            'mouthfeel' => 'Heavy',
            'Sweetness' => 'Sweet'
        ]);
    }
}
