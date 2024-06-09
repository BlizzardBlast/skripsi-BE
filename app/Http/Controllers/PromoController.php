<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use App\Models\PromoUsage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\OrderController;
use PhpParser\Node\Stmt\Else_;

class PromoController extends Controller
{
    private function getPromoUsage($promo_id, $user_id = null)
    {
        if ($user_id) {
            return PromoUsage::where('promo_id', $promo_id)
                ->where('user_id', $user_id)
                ->count();
        } else {
            return PromoUsage::where('promo_id', $promo_id)
                ->count();
        }
    }

    // return null if invalid, 9 if no promo code, else returns discount
    public function verifyPromoAndReturnDiscount($promo_code, $total_price)
    {
        if (!$promo_code) {
            return 0;
        }

        $promo = Promo::where('promo_code', $promo_code)
            ->where('promo_start_date', '<=', Carbon::now()->startOfDay()) // Check promo_start_date
            ->where('promo_expiry_date', '>=', Carbon::now()->startOfDay())
            ->first();
        $exceeded_max_use = $promo->max_use > $this->getPromoUsage($promo->id);
        $exceeded_max_use_per_user = $promo->max_use_per_user > $this->getPromoUsage($promo->id, Auth::user()->id);
        $minimum_not_met = isset($promo->minimum) && $total_price < $promo->minimum;

        if (!$promo) {return "NP";} // No Promo found
        else if ($exceeded_max_use) {return "MU";} // Max Use exceeded
        else if ($exceeded_max_use_per_user) {return "MUU";} // Max Use per User exceeded
        else if ($minimum_not_met) {return "MP";} // Minimum Price not met
        else
        {
            $discountAmount = ($total_price * $promo->discount) / 100;

            if ($promo->maximum > 0 && $discountAmount > $promo->maximum) {
                $discountAmount = $promo->maximum;
            }

            return $discountAmount;
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
            ]);

            $promoCode = $validatedData['promo_code'];
            $orderController = new OrderController();
            $totalPrice = $orderController->getCartTotalPrice();

            $discountAmount = $this->verifyPromoAndReturnDiscount($promoCode, $totalPrice);

            if (is_integer($discountAmount)) {
                return response()->json(['discount' => $discountAmount], 200);
            } elseif ($discountAmount == "NP") {
                return response()->json(['message' => 'Promo Denied. Invalid promo code.'], 400);
            } elseif ($discountAmount == "MU") {
                return response()->json(['message' => 'Promo Denied. Promo has expired.'], 400);
            } elseif ($discountAmount == "MUU") {
                return response()->json(['message' => 'Promo Denied. You cannot use this promo any more.'], 400);
            } elseif ($discountAmount == "MP") {
                return response()->json(['message' => 'Promo Denied. Minimum total price not met.'], 400);
            } else {
                return response()->json(['message' => 'Promo Denied. Unkown Error.'], 400);
            }
        } catch (Exception) {
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
                'promo_start_date' => 'required',
                'promo_expiry_date' => 'required',
                'discount' => 'required|integer',
                'minimum' => 'nullable|integer',
                'maximum' => 'nullable|integer',
                'max_use' => 'required|integer',
                'max_use_per_user' => 'required|integer'
            ]);
            $validatedData['promo_start_date'] = Carbon::parse($validatedData['promo_start_date'])->format('Y-m-d H:i:s');
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
