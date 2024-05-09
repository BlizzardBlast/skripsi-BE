<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function getAllUserCart()
    {
        if (!Auth::check()) {
            return response()->json(null, 200);
        }

        $results = Cart::where('user_id', Auth::user()->id)->get();

        $response = [];

        foreach ($results as $data) {

            $product = Product::find($data['product_id']);

            if ($product) {
                // If product exists, construct the response
                $response[] = [
                    'userId' => $data['userId'],
                    'quantity' => $data['quantity'],
                    'product' => $product // Include the product details
                ];
            } else {
                // If product doesn't exist, you might handle this case differently
                // For example, return an error message or skip this entry
            }
        }


        return response()->json($response);
    }

    public function addToCart(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(null, 200);
        }

        $valid = $request->validate([
            'productId' => ['required', 'integer'],
            'quantity' => ['required', 'integer']
        ]);

        //KALO UDAH ADA PRODUCTNYA



        if (Cart::where([
            ['user_id', Auth::user()->id],
            ['product_id', $valid['productId']]
        ])->exists()) {
            // Product already exists in the cart, so increment the quantity
            $this->editQty($request);
        } else {
            // Product doesn't exist in the cart, so add the product
            $data = [
                'user_id' => Auth::user()->id,
                'product_id' => $valid['productId'],
                'quantity' => $valid['quantity']
            ];
            Cart::create($data);
        }
    }

    public function editQty(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(null, 200);
        }

        $valid = $request->validate([
            'productId' => ['required', 'integer'],
            'quantity' => ['required', 'integer']
        ]);

        $old = Cart::where([
            ['user_id', Auth::user()->id],
            ['product_id', $valid['productId']]
        ]);

        if ($old->exists()) {
            $valid['quantity'] += $old->first()->quantity;
        }

        if ($valid['quantity'] <= 0) {
            $this->removeFromCart($request);
        } else {
            Cart::where([
                ['user_id', Auth::user()->id],
                ['product_id', $valid['productId']],
            ])->update([
                'quantity' => $valid['quantity']
            ]);
        }
    }

    public function removeFromCart(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(null, 200);
        }

        $valid = $request->validate([
            'productId' => ['required', 'integer']
        ]);

        Cart::where([
            ['user_id', Auth::user()->id],
            ['product_id', $valid['productId']],
        ])->delete();
    }
}
