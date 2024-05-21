<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Product;
use App\Models\Preference;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOption\None;

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
        if (!Auth::check()) {
            return response()->json(null, 400);
        }

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


    public function getUserPreferences($refresh)
    {
        if (!Auth::check() || !isset(Auth::user()->preference)) {
            return response()->json(null, 400);
        }

        if ($refresh != "true") {
            $ids = Preference::select('product_id')
                ->where('user_id', Auth::user()->id)
                ->limit(3)
                ->get()
                ->toArray();

            $results = Product::select('*')
                ->whereIn('id', $ids)
                ->get();

            return response()->json($results, 200);
        } else {
            $user = Auth::user();
            $preference = json_decode($user->preference, true);

            $sql_dyn = [];
            foreach ($preference as $attrName => $attrVal) {
                $sql_dyn[] = "CASE WHEN " . $attrName . " = '" . $attrVal . "' THEN 1 ELSE 0 END";
            }

            $sql_dyn = implode(" + ", $sql_dyn);
            $ids = ProductAttribute::select('*')
                ->selectRaw($sql_dyn . " as score")
                ->orderBy('score')
                ->limit(3)
                ->get()
                ->toArray();
            
            Preference::where('user_id', $user->id)->delete();

            $results = Product::whereIn('id',$ids);
            foreach ($results as $row) {
                $data = [
                    'user_id' => $user->id,
                    'product_id' => $row->id,
                    'score' => floatval($row->score)
                ];
                Preference::create($data);
            }

            return response()->json($results, 200);
        }
    }


    public function addProduct(Request $request)
    {
        if (!Auth::check() || Auth::user()->role != 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'name' => 'required',
                'subname' => 'required',
                'origin' => 'required',
                'type' => 'required',
                'price' => 'required|integer',
                'description' => 'required',
                'image' => 'nullable|mimes:jpeg,png,jpg|max:3072',
                'acidity' => 'required',
                'flavor' => 'required',
                'aftertaste' => 'required',
                'sweetness' => 'required'
            ]);

            $nextProductId = DB::table('products')->max('id') + 1;
            $pictureFilename = $nextProductId . 'C.png';

            if ($request->hasFile('image')) {
                $coffeePicture = $request->file('image');
                $coffeePicture->storeAs('public/coffeeImage', $pictureFilename);

                $validatedData['image'] = $pictureFilename;
            }
            $newProduct = Product::create([
                'name' => $validatedData['name'],
                'subname' => $validatedData['subname'],
                'origin' => $validatedData['origin'],
                'type' => $validatedData['type'],
                'price' => $validatedData['price'],
                'description' => $validatedData['description'],
                'image' => $validatedData['image'],
            ]);

            ProductAttribute::create([
                'product_id' => $newProduct->id,
                'acidity' => $validatedData['acidity'],
                'flavor' => $validatedData['flavor'],
                'aftertaste' => $validatedData['aftertaste'],
                'sweetness' => $validatedData['sweetness']
            ]);

            return response()->json(['message' => 'Successfully added Product.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to add product.', 'error' => $e->getMessage()], 400);
        }
    }

    public function editProduct(Request $request, $id)
    {
        if (!Auth::check() || Auth::user()->role != 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'new_name' => 'nullable|string',
                'new_subname' => 'nullable|string',
                'new_origin' => 'nullable|string',
                'new_type' => 'nullable|string',
                'new_price' => 'nullable|integer',
                'new_description' => 'nullable|string',
                'new_image' => 'nullable|mimes:jpeg,png,jpg|max:3072',
                'new_acidity' => 'nullable|string',
                'new_flavor' => 'nullable|string',
                'new_aftertaste' => 'nullable|string',
                'new_sweetness' => 'nullable|string',
            ]);

            $product = Product::findOrFail($id);
            $productAttribute = ProductAttribute::where('product_id',$id)->firstOrFail();

            $updateData = [
                'name' => $validatedData['new_name'] ?? $product->name,
                'subname' => $validatedData['new_subname'] ?? $product->subname,
                'origin' => $validatedData['new_origin'] ?? $product->origin,
                'type' => $validatedData['new_type'] ?? $product->type,
                'price' => $validatedData['new_price'] ?? $product->price,
                'description' => $validatedData['new_description'] ?? $product->description
            ];
            $updateAttributeData = [
                'acidity' => $validatedData['new_acidity'] ?? $product->acidity,
                'flavor' => $validatedData['new_flavor'] ?? $product->flavor,
                'aftertaste' => $validatedData['new_aftertaste'] ?? $product->aftertaste,
                'sweetness' => $validatedData['new_sweetness'] ?? $product->sweetness
            ];

            if ($request->hasFile('new_image')) {
                $pictureFilename = $product->id . 'C.png';
                if ($product->image) {
                    Storage::delete('public/coffeeImage/' . $product->image);
                }

                $coffeePicture = $request->file('new_image');
                $coffeePicture->storeAs('public/coffeeImage', $pictureFilename);
                $updateData['image'] = $pictureFilename;
            }

            $product->update($updateData);
            $productAttribute->update($updateAttributeData);


            return response()->json(['message' => 'Successfully updated Product.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to update product.', 'error' => $e->getMessage()], 400);
        }
    }

    public function removeProduct($id)
    {
        if (!Auth::check() || Auth::user()->role != 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $product = Product::findOrFail($id);

            if ($product->image) {
                Storage::delete('public/coffeeImage/' . $product->image);
            }
            $product->delete();
            ProductAttribute::where('product_id',$id)->delete();

            return response()->json(['message' => 'Successfully deleted product'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to delete product.', 'error' => $e->getMessage()], 400);
        }
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
