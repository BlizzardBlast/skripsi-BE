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
            'name' => 'Rwanda Mibirizi',
            'subname' => 'Coffee Arabica',
            'origin' => 'Rwanda',
            // 'characteristic' => 'silky, creamy body along with the floral notes of Ethiopian Yirgacheffe Coffee and the acidity of a Kenya coffee.',
            'type' => 'Arabica',
            'price' => '5000',
            'description' => 'Amazing coffees from Rwanda often have a fruitiness and freshness reminiscent of red
            apples or red grapes. Berry fruit flavours and floral qualities are also fairly common.',

            'acidity' => 'high',
            'flavor' => 'fruit',
            'aftertaste' => 'short',
            'sweetness' => 'rich'

        ]);

        DB::table('products')->insert([
            'name' => 'Tanzania Kilimanjaro',
            'subname' => 'Canis Betares',
            'origin' => 'Tanzania',
            'type' => 'Robusta',
            'price' => '7000',
            'description' => 'Tanzania coffee tends to be complex, with bright and lively acidity and often with berry and fruity flavours,
            Tanzanian coffees can be juicy, interesting and delicious.',

            'acidity' => 'high',
            'flavor' => 'fruit',
            'aftertaste' => 'lingering',
            'sweetness' => 'noticeable'
        ]);
        ////
        DB::table('products')->insert([
            'name' => 'Uganda Bugisu',
            'subname' => 'Canis Charles',
            'origin' => 'Uganda',
            'type' => 'Arabica',
            'price' => '9000',
            'description' => 'Exceptional coffee from Uganda is still relatively rare, but the best cups are sweet, full
            of dark fruits and have a clean finish.',

            'acidity' => 'medium',
            'flavor' => 'fruit',
            'aftertaste' => 'lingering',
            'sweetness' => 'noticeable'
        ]);

        DB::table('products')->insert([
            'name' => 'China Yunan',
            'subname' => 'Canis Charles',
            'origin' => 'China',
            'type' => 'Bourbon',
            'price' => '9000',
            'description' => 'The better coffees coming from China have a pleasant sweetness and fruitiness, though
            many still carry a little woodiness or earthiness too. Relatively low in acidity and often
            relatively full-bodied.
            ',

            'acidity' => 'high',
            'flavor' => 'fruit',
            'aftertaste' => 'lingering',
            'sweetness' => 'rich'
        ]);

        DB::table('products')->insert([
            'name' => 'Indonesia Sumatera',
            'subname' => 'Canis Charles',
            'origin' => 'Indonesia',
            'type' => 'Bourbon',
            'price' => '9000',
            'description' => 'Semi-washed coffees tend to be very heavy bodied, earthy, woody and spicy with very
            little acidity.',
            'acidity' => 'low',
            'flavor' => 'earthy',
            'aftertaste' => 'complex',
            'sweetness' => 'rich'
        ]);



        DB::table('products')->insert([
            'name' => 'Honduran Copan',
            'subname' => 'Canis Charles',
            'origin' => 'Honduras',

            'type' => 'Robusta',
            'price' => '9000',
            'description' => 'A range of different flavours are found in Honduran coffees, but the best often have a
            complex fruity quality, and a lively, juicy acidity.',

            'acidity' => 'medium',
            'flavor' => 'fruit',
            'aftertaste' => 'short',
            'sweetness' => 'noticeable'
        ]);

        DB::table('products')->insert([
            'name' => 'Mexican Chiapas',
            'subname' => 'Canis Charles',
            'origin' => 'Mexico',

            'type' => 'Bourbon',
            'price' => '9000',
            'description' => 'Mexico coffee consist of quite a range of coffees across its regions, from lighter-bodied,
            delicate coffees through to sweeter coffees with caramel, toffee or chocolate flavours in
            the cup.',

            'acidity' => 'medium',
            'flavor' => 'chocolate',
            'aftertaste' => 'short',
            'sweetness' => 'noticeable'
        ]);

        DB::table('products')->insert([
            'name' => 'Nicaragua Jinotega',
            'subname' => 'Canis Charles',
            'origin' => 'Nicaragua',

            'type' => 'Caturra',
            'price' => '9000',
            'description' => 'Nicaragua coffee are typically quite complex
            and capable of pleasing fruit-like flavours and clean acidity.',

            'acidity' => 'medium',
            'flavor' => 'fruit',
            'aftertaste' => 'complex',
            'sweetness' => 'noticeable'
        ]);

        DB::table('products')->insert([
            'name' => 'Peru Cajamarca',
            'subname' => 'Canis Charles',
            'origin' => 'Peru',

            'type' => 'Bourbon',
            'price' => '9000',
            'description' => 'Typically Peruvian coffees have been clean, but a little soft and flat. They are sweet and
            relatively heavy bodied but not very complex. Increasingly there are distinctive and
            juicier coffees becoming available.',

            'acidity' => 'medium',
            'flavor' => 'earthy',
            'aftertaste' => 'complex',
            'sweetness' => 'noticeable'
        ]);

        DB::table('products')->insert([
            'name' => 'Peru Cajamarca',
            'subname' => 'Canis Charles',
            'origin' => 'Peru',

            'type' => 'Bourbon',
            'price' => '9000',
            'description' => 'Typically Peruvian coffees have been clean, but a little soft and flat. They are sweet and
            relatively heavy bodied but not very complex. Increasingly there are distinctive and
            juicier coffees becoming available.',

            'acidity' => 'medium',
            'flavor' => 'earthy',
            'aftertaste' => 'short',
            'sweetness' => 'faint'
        ]);


        DB::table('products')->insert([
            'name' => 'Kopi Luwak',
            'subname' => 'Canis Charles',
            'origin' => 'Indonesia',
            'type' => 'Arabica/Robusta',
            'price' => '9000',
            'description' => '',
            'acidity' => 'low',
            'flavor' => 'earthy',
            'aftertaste' => 'complex',
            'sweetness' => 'faint'
        ]);

        DB::table('products')->insert([
            'name' => 'Venezuelan Coffee',
            'subname' => 'Canis Charles',
            'origin' => 'Venezuela',
            'type' => 'Bourbon',
            'price' => '9000',
            'description' => 'The better coffees from Venezuela are quite sweet, a little low in acidity and relatively
            rich in terms of mouthfeel and texture.',
            'acidity' => 'low',
            'flavor' => 'chocolate',
            'aftertaste' => 'lingering',
            'sweetness' => 'rich'
        ]);

        DB::table('products')->insert([
            'name' => 'Hawaian Kona',
            'subname' => 'Canis Charles',
            'origin' => 'Hawai USA',
            'type' => 'Arabica',
            'price' => '9000',
            'description' => 'Hawaiian coffee, particularly Kona coffee from the Big Island of Hawaii,
            is renowned for its unique characteristics that make it highly sought after by coffee enthusiasts worldwide.',
            'acidity' => 'medium',
            'flavor' => 'nutty',
            'aftertaste' => 'lingering',
            'sweetness' => 'rich'
        ]);

        DB::table('products')->insert([
            'name' => 'Colombian Manizales',
            'subname' => 'Canis Charles',
            'origin' => 'Colombia',
            'type' => 'Arabica',
            'price' => '9000',
            'description' => 'Colombian coffee is renowned worldwide for its exceptional quality, rich flavor, and balanced characteristics.',
            'acidity' => 'medium',
            'flavor' => 'nutty',
            'aftertaste' => 'short',
            'sweetness' => 'rich'
        ]);
    }
}
