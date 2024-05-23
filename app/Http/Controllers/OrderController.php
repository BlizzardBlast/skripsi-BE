<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    //
    public function getOrder()
    {
        if (!Auth::check()) {
            return response()->json(null, 400);
        }

        $order = Order::where('user_id', Auth::user()->id)->get();
        return response()->json($order);
    }

    public function getOrderSpecific($id)
    {
        if (!Auth::check()) {
            return response()->json(null, 400);
        }

        $orderSpecific = OrderDetail::with('product')->where('order_id', $id)->get();

        return response()->json($orderSpecific);
    }

    public function postOrder(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(null, 400);
        } 
        
        $cart = Cart::where('user_id', Auth::user()->id)->with('product')->get();

        $total_price = 0;
        foreach ($cart as $c) {
            $total_price += $c->product->price * $c->qty; 
        }

        // Create the order
        $order = Order::create([
            'user_id' => Auth::user()->id,
            'confirmation' => "Confirmed",
            'total_price' => $total_price,
        ]);

        // Create the order details
        foreach ($cart as $c) {

            OrderDetail::create([
                'quantity' => $c->qty,
                'product_id' => $c->qty,
                'order_id' => $order->id,
                'user_id' => Auth::user()->id,
            ]);
        }

         Cart::where('user_id', Auth::user()->id)->delete();

        return response()->json(['message' => 'Successfully added new order with details']);
    }
}
