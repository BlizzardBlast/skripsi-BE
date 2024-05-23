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

        $validatedData = $request->validate([
            'confirmation' => 'required',
            'total_price' => 'required|numeric|min:0',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|integer|min:1',
        ]);

        // Create the order
        $order = Order::create([
            'user_id' => Auth::user()->id,
            'confirmation' => $validatedData['confirmation'],
            'total_price' => $validatedData['total_price'],
        ]);

        // Create the order details
        foreach ($validatedData['details'] as $orderDetailData) {
            if (!isset($orderDetailData['product_id']) || !isset($orderDetailData['quantity'])) {
                return response()->json(['message' => 'Missing product_id or quantity in details'], 400);
            }

            OrderDetail::create([
                'quantity' => $orderDetailData['quantity'],
                'product_id' => $orderDetailData['product_id'],
                'order_id' => $order->id,
                'user_id' => Auth::user()->id,
            ]);
        }

        // Delete the cart items
        if (Auth::user()) {
            Cart::where('user_id', Auth::user()->id)->delete();
        }

        return response()->json(['message' => 'Successfully added new order with details']);
    }
}
