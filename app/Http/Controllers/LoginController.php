<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class LoginController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // sign up
    // password: min 8, 1 lower, 1 upper, 1 digit
    public function signup(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|min:3|max:255',
            'username' => 'required|min:3|max:255|unique:users',
            'email' => 'required|email:dns|unique:users',
            'password' => 'required|min:8|max:255|regex:/^(?=.[a-z])(?=.[A-Z])(?=.*\d).{8,}$/',
        ]);

        $validatedData['password'] = bcrypt($validatedData['password']);


        User::create($validatedData);

        return redirect('/signIn');
    }

    // sign in
    public function signin(Request $request)
    {
        $valid = null;

        try {
            $valid = $request->validate([
                'email' => ['required', 'email:dns'],
                'password' => ['required']
            ]);

            // remember email
            // if ($request->checkbox) {
            //     Cookie::queue('mycookie', $request->email, 120);
            // }

            // success
            if (Auth::attempt($valid)) {
                return response()->json(['message' => 'Sign In Successful!'], 200);
            }
        } catch (Exception $e) {
            // fail
            return response()->json(['message' => 'Sign In Failed.'], 422);
        }
    }

    // return user data for FE
    public function get_user_data()
    {
        if (!Auth::check()) {
            return response()->json(null, 200);
        }

        $user = Auth::user();
        $specific = [
            'username' => $user->username,
            'email' => $user->email,
        ];

        return response()->json($specific);
    }
}
