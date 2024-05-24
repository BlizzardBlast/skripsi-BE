<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use App\Models\PromoUsage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PromoController extends Controller
{
    private function getPromoUsage($promo_code, $user_id = null)
    {

        $promoId = Promo::where('promo_code', $promo_code)->pluck('id')->first();
        if ($user_id) {
            return PromoUsage::where('promo_id', $promoId)
                ->where('user_id', $user_id)
                ->count();
        } else {
            return PromoUsage::where('promo_id', $promoId)
                ->count();
        }
    }

    public function checkPromo(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(null, 400);
        }

        try {
            $validatedData = $request->validate([
                'promo_code' => 'required|string',
                'total_price' => 'nullable|numeric',
            ]);

            $promoCode = $validatedData['promo_code'];
            $totalPrice = $validatedData['total_price'];

            $promo = Promo::where('promo_code', $promoCode)
                ->where('promo_expiry_date', '>=', Carbon::now())
                ->where('max_use', '>', $this->getPromoUsage($promoCode))
                ->where('max_use_per_user', '>', $this->getPromoUsage($promoCode, Auth::user()->id))
                ->first();

            if ($promo) {
                if (isset($promo->minimum) && $totalPrice < $promo->minimum) {
                    return response()->json(['message' => 'Promo Denied. Minimum total price not met.'], 400);
                }
                $discountAmount = ($totalPrice * $promo->discount) / 100;

                if ($promo->maximum > 0 && $discountAmount > $promo->maximum) {
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
        if (!Auth::check() || Auth::user()->role != 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $validatedData = $request->validate([
                'promo_code' => 'required',
                'promo_expiry_date' => 'required',
                'discount' => 'required|integer',
                'minimum' => 'nullable|integer',
                'maximum' => 'nullable|integer',
                'max_use' => 'required|integer',
                'max_use_per_user' => 'required|integer'
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
        if (!Auth::check() || Auth::user()->role != 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $allPromo = Promo::all();
        return response()->json($allPromo);
    }

    public function deletePromo($id)
    {
        if (!Auth::check() || Auth::user()->role != 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $promo = Promo::findOrFail($id);

            $promo->delete();

            return response()->json(['message' => 'Successfully deleted promo'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to delete promo', 'error' => $e->getMessage()], 400);
        }
    }
}
