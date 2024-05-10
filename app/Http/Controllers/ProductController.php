<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    //
    public function getAllProduct()
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

    public function setUserPreferences(Request $request)
    {
        $validatedData = $request->validate([
            'acidity' => ['required', 'string', Rule::in(['low', 'medium', 'high'])],
            'flavor' => ['required', 'string', Rule::in(['earthy', 'chocolate', 'fruit', 'nutty'])],
            'aftertaste' => ['required', 'string', Rule::in(['complex', 'lingering', 'short'])],
            'sweetness' => ['required', 'string', Rule::in(['faint', 'noticeable', 'rich'])]
        ]);
        $userPref = [
            'acidity' => $validatedData['acidity'],
            'flavor' => $validatedData['flavor'],
            'aftertaste' => $validatedData['aftertaste'],
            'sweetness' => $validatedData['sweetness']
        ];

        $userPref = json_encode($userPref);

        User::where('id', Auth::user()->id)->update([
            'preference' => $userPref
        ]);

        return response()->json(null, 200);
    }


    public function getUserPreferences()
    {
        if (!Auth::check() || !isset(Auth::user()->preference)) {
            return response()->json(null, 200);
        }

        $user = Auth::user();
        $preference = json_decode($user->preference, true);

        $sql_dyn = [];
        foreach ($preference as $attrName => $attrVal) {
            $sql_dyn[] = "CASE WHEN " . $attrName . " = '" . $preference[$attrName] . "' THEN 1 ELSE 0 END";
        }

        $sql_dyn = implode(" + ", $sql_dyn);
        $results = Product::select('*')
            ->selectRaw($sql_dyn . " as score")
            ->orderBy('score')
            ->limit(3)
            ->get();

        return response()->json($results);
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