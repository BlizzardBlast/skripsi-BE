<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

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
