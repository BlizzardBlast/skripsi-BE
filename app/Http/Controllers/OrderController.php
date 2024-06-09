<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromoController;

class OrderController extends Controller
{
    public function getCartTotalPrice($cart = null)
    {
        $total_price = 0;

        // query reuse
        if (!$cart) {
            $cart = Cart::where('user_id', Auth::user()->id)->with('product')->get();
        }

        foreach ($cart as $c) {
            $total_price += $c->product->price * $c->quantity;
        }

        return $total_price;
    }

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
            'promo_code' => 'nullable|string',
        ]);

        $cart = Cart::where('user_id', Auth::user()->id)->with('product')->get();
        $total_price = $this->getCartTotalPrice($cart);

        $promoController = new PromoController();
        $discountAmount = $promoController->verifyPromoAndReturnDiscount($validatedData['promo_code'], $total_price);
        $discountAmount = is_integer($discountAmount) ? $discountAmount : 0; // if invalid promocode

        // Create the order
        $order = Order::create([
            'user_id' => Auth::user()->id,
            'confirmation' => "Confirmed",
            'total_price' => $total_price,
            'discount_amount' => $discountAmount,
        ]);

        // Create the order details
        foreach ($cart as $c) {
            OrderDetail::create([
                'quantity' => $c->quantity,
                'roasting_type' => $c->roasting_type,
                'product_id' => $c->product_id,
                'order_id' => $order->id,
                'user_id' => $c->user_id,
            ]);
        }

        Cart::where('user_id', Auth::user()->id)->delete();

        return response()->json(['message' => 'Successfully added new order with details'], 200);
    }
}
