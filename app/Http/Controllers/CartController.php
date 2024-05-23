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
            return response()->json(null, 400);
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


        return response()->json($response, 200);
    }

    public function addToCart(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(null, 400);
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
        return response()->json(['message' => 'Successfully added to Cart'], 200);
    }

    public function editQty(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(null, 400);
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

        return response()->json(['message' => 'Successfully changed quantity'], 200);
    }

    public function incrementQuantity(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(null, 400);
        }

        $valid = $request->validate([
            'productId' => ['required', 'integer'],
            'quantity' => ['required', 'integer']
        ]);

        $previousData = Cart::where([
            ['user_id', Auth::user()->id],
            ['product_id', $valid['productId']]
        ]);

        if ($previousData->exists()) {
            $valid['quantity'] += $previousData->first()->quantity;
        }

        Cart::where([
            ['user_id', Auth::user()->id],
            ['product_id', $valid['productId']],
        ])->update([
            'quantity' => $valid['quantity']
        ]);

        return response()->json(['message' => 'Successfully incremented quantity.'], 200);
    }

    public function decrementQuantity(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(null, 400);
        }

        $valid = $request->validate([
            'productId' => ['required', 'integer'],
            'quantity' => ['required', 'integer']
        ]);

        $previousData = Cart::where([
            ['user_id', Auth::user()->id],
            ['product_id', $valid['productId']]
        ]);

        if ($previousData->exists()) {
            $temp = $previousData->first()->quantity - $valid['quantity'];

            if ($temp <= 0) {
                Cart::where([
                    ['user_id', Auth::user()->id],
                    ['product_id', $valid['productId']],
                ])->delete();
            } else {
                Cart::where([
                    ['user_id', Auth::user()->id],
                    ['product_id', $valid['productId']],
                ])->update([
                    'quantity' => $temp
                ]);
            }
        }
        return response()->json(['message' => 'Successfully decremented quantity.'], 200);
    }

    public function removeFromCart(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(null, 400);
        }

        $valid = $request->validate([
            'productId' => ['required', 'integer']
        ]);

        Cart::where([
            ['user_id', Auth::user()->id],
            ['product_id', $valid['productId']],
        ])->delete();

        return response()->json(['message' => 'Successfully removed item from Cart.'], 200);
    }
}
