<?php

namespace App\Http\Controllers;

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
        $orderSpecific = OrderDetail::where('order_id', $id)->get();
        return response()->json($orderSpecific);
    }

    public function postOrder(Request $request)
    {
        $validatedData = $request->validate([
            'confirmation' => 'required',
            'total_price' => 'required',
        ]);

        // Create the order
        $order = Order::create([
            'user_id' => Auth::user()->id,
            'confirmation' => $validatedData['confirmation'],
            'total_price' => $validatedData['total_price'],
        ]);



        return response()->json(['message' => 'Successfully added new order with details']);
    }

    public function postOrderDetail(Request $request){
        // Validate the request data
        $validatedData = $request->validate([
            '*.product_id' => 'required|exists:products,id', // Validate product_id for each order detail
            '*.quantity' => 'required|integer|min:1', // Validate quantity for each order detail
        ]);

        $lastOrderId = Order::max('id');

        $nextOrderId = $lastOrderId + 1;

        foreach ($validatedData as $orderDetailData) {
            OrderDetail::create([
                'order_id' => $nextOrderId,
                'user_id' => Auth::user()->id,
                'product_id' => $orderDetailData['product_id'],
                'quantity' => $orderDetailData['quantity'],
            ]);
        }

        return response()->json(['message' => 'Successfully added order details']);
    }
}
