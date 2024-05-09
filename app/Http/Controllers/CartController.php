<?php

namespace App\Http\Controllers;

use App\Models\Cart;
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


        foreach($results as $data){

            $product = Product::find($data['product_id']);

            if ($product) {
                // If product exists, construct the response
                $response[] = [
                    'userId' => $data['userId'],
                    'qty' => $data['qty'],
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
            'qty' => ['required', 'integer']
        ]);

        if (Cart::where([
            ['user_id', Auth::user()->id],
            ['product_id', $valid['productId']]
        ])->exists() >= 1) {
            $this->editQty($request);
        } else {
            $data = [
                'user_id' => Auth::user()->id,
                'product_id' => $valid['productId'],
                'qty' => $valid['qty']
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
            'qty' => ['required', 'integer']
        ]);

        $old = Cart::where([
            ['user_id', Auth::user()->id],
            ['product_id', $valid['productId']]
        ]);

        if ($old->exists()) {
            $valid['qty'] += $old->first()->qty;
        }

        if ($valid['qty'] <= 0) {
            $this->removeFromCart($request);
        } else {
            Cart::where([
                ['user_id', Auth::user()->id],
                ['product_id', $valid['productId']],
            ])->update([
                'qty' => $valid['qty']
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
