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

        Cart::where('user_id', Auth::user()->id)->get();
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

        $data = [
            'user_id' => Auth::user()->id,
            'product_id' => $valid['productId'],
            'qty' => $valid['qty']
        ];

        Cart::create($data);
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
            'productId' => ['required', 'integer'],
            'qty' => ['required', 'integer']
        ]);

        Cart::where([
            ['user_id', Auth::user()->id],
            ['product_id', $valid['productId']],
        ])->delete();
    }
}
