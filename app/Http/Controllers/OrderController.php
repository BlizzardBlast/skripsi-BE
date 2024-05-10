<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    //
    public function getOrder(){
        if (!Auth::check()) {
            return response()->json(null, 200);
        }

        $order = Order::where('user_id', Auth::user()->id)->get();
        return response()->json($order);
    }

    public function getOrderSpecific($id){
        if (!Auth::check()) {
            return response()->json(null, 200);
        }
        $orderSpecific = OrderDetail::where('order_id', $id)->get();
        return response()->json($orderSpecific);
    }

    public function postOrder(Request $request){
        $validatedData = $request->validate([
            'user_id' => 'required',
            'confirmation' => 'required',
            'total_price' => 'required',
            'order_details' => 'required|array|min:1', // Ensure order_details is an array with at least one element
            'order_details.*.quantity' => 'required|integer|min:1', // Validate quantity for each order detail
            'order_details.*.product_id' => 'required|exists:products,id', // Validate product_id for each order detail
        ]);

        // Create the order
        $order = Order::create([
            'user_id' => $validatedData['user_id'],
            'confirmation' => $validatedData['confirmation'],
            'total_price' => $validatedData['total_price'],
        ]);

        // Create order details for the order
        foreach ($validatedData['order_details'] as $orderDetailData) {
            OrderDetail::create([
                'order_id' => $order->id,
                'user_id' => $validatedData['user_id'], // Assuming order details inherit user_id from the order
                'product_id' => $orderDetailData['product_id'],
                'quantity' => $orderDetailData['quantity'],
            ]);
        }

        return response()->json(['message' => 'Successfully added new order with details']);
    }


}
