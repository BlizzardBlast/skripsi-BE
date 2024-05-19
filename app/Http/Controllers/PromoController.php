<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PromoController extends Controller
{
    public function checkPromo(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'promo_code' => 'required|string',
                'total_price' => 'nullable|numeric',
            ]);

            $promoCode = $validatedData['promo_code'];
            $totalPrice = $validatedData['total_price'];

            $promo = Promo::where('promo_code', $promoCode)
                ->where('promo_expiry_date', '>=', Carbon::now())
                ->first();

            if ($promo) {
                if (isset($promo->minimum) && $totalPrice < $promo->minimum) {
                    return response()->json(['message' => 'Promo Denied. Minimum total price not met.'], 400);
                }
                $discountAmount = ($totalPrice * $promo->discount) / 100;

                $discount = $promo->discount;
                if ($promo->maximum > 0 && $discount > $promo->maximum) {
                    $discountAmount = $promo->maximum;
                }

                return response()->json(['discount' => $discountAmount], 200);
            } else {
                return response()->json(['message' => 'Promo Denied. Invalid promo code or expired.'], 400);
            }
        } catch (Exception $e) {
            return response()->json(['message' => 'Promo Denied. An error occurred.'], 400);
        }
    }


    public function postPromo(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $validatedData = $request->validate([
                'promo_code' => 'required',
                'promo_expiry_date' => 'required',
                'discount' => 'required',
                'minimum' => 'nullable',
                'maximum' => 'nullable'
            ]);

            $validatedData['promo_expiry_date'] = Carbon::parse($validatedData['promo_expiry_date'])->format('Y-m-d H:i:s');

            Promo::create($validatedData);

            return response()->json(['message' => 'Successfully issued a new promo.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to issue a new promo.'], 400);
        }
    }


    public function getAllPromo()
    {
        $allPromo = Promo::all();
        return response()->json($allPromo);
    }

    public function deletePromo($id)
    {
        try {
            $promo = Promo::findOrFail($id);

            $promo->delete();

            return response()->json(['message' => 'Successfully deleted promo'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to delete promo', 'error' => $e->getMessage()], 400);
        }
    }
}
