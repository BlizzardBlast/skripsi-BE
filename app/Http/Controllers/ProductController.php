<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    //
    public function getAllProduct(Request $request)
    {
        $allProduct = Product::all();
        return response()->json($allProduct);
    }

    public function filterByBean($bean)
    {
        $coffeeBeanType = $bean;

        $filterProduct = Product::where('type', $coffeeBeanType)->get();
        return response()->json($filterProduct);
    }

    public function getProductImage($id)
    {
        $filePath = storage_path("app\public\coffeeImage\\" . $id . "C.png");

        $fileContent = file_get_contents($filePath);
        $base64 = 'data:image/png;base64,' . base64_encode($fileContent);

        return response()->json(['image_base64' => $base64], 200);
    }

    public function getUserPreferences()
    {
        if (!Auth::check() || isset(Auth::user()->preference)) {
            return response()->json(null, 200);
        }

        $user = Auth::user();
        $preference = json_decode($user->preference, true);

        $sql = "SELECT id, name, subname, origin, characteristic, type, price, description";
        $sql_dyn = [];
        $sql_data = [];
        foreach ($preference as $attrName => $attrVal) {
            $sql_dyn[] = "CASE WHEN " . $attrName . " = ? THEN 1 ELSE 0 ";
            $sql_data[] = $preference[$attrName];
        }

        $sql .= implode("+", $sql_dyn) . " as score ORDER BY score DESC LIMIT 3";
        $result = DB::select($sql, $sqlData);
        return response()->json($esult);
    }



    // public function getSortedProductByName()
    // {
    //     $sortedProduct = Product::orderBy('name')->get();
    //     return response()->json($sortedProduct);
    // }

    // public function getSortedProductByPrice()
    // {

    //     $sortedProducts = Product::orderBy('price')->get();

    //     return response()->json($sortedProducts);
    // }
}
