<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    //
    public function getOrder()
    {
        if (!Auth::check()) {
            return response()->json(null, 200);
        }

        $order = Order::where('user_id', Auth::user()->id)->get();
        return response()->json($order);
    }

    public function getOrderSpecific($id)
    {
        if (!Auth::check()) {
            return response()->json(null, 200);
        }

        $orderSpecific = OrderDetail::with('product')->where('order_id', $id)->first();

        return response()->json($orderSpecific);
    }

    public function postOrder(Request $request)
    {
        $validatedData = $request->validate([
            'confirmation' => 'required',
            'total_price' => 'required',
        ]);

        // Create the order
        Order::create([
            'user_id' => Auth::user()->id,
            'confirmation' => $validatedData['confirmation'],
            'total_price' => $validatedData['total_price'],
        ]);

        return response()->json(['message' => 'Successfully added new order with details']);
    }

    public function postOrderDetail(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            '*.product_id' => 'exists:products,id', // Validate product_id for each order detail
            '*.quantity' => 'integer|min:1', // Validate quantity for each order detail
        ]);

        $lastOrderId = Order::max('id');

        foreach ($validatedData as $orderDetailData) {
            OrderDetail::create([
                'order_id' => $lastOrderId,
                'user_id' => Auth::user()->id,
                'product_id' => $orderDetailData['product_id'],
                'quantity' => $orderDetailData['quantity'],
            ]);
        }

        Cart::where([['user_id', Auth::user()->id]])->delete();

        return response()->json(['message' => 'Successfully added order details']);
    }
}
