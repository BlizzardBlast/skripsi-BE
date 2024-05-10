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
        ]);


        Order::create($validatedData);


        return response()->json(['message' => 'Successfully updated user profile']);

    }


}
