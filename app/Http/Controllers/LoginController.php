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

    public function signOut(Request $request)
    {
        Auth::guard(name: 'web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // return redirect('/signIn');
        return response()->json(['message' => 'Signed Out.'], 200);
    }
    public function signUp(Request $request)
    {
        try {


            $validatedData = $request->validate([
                'name' => 'required|min:3|max:255',
                'username' => 'required|min:3|max:255',
                'email' => 'required|email:dns|unique:users',
                'password' => 'required|min:8|max:255|regex:/^(?=.*[a-z])(?=.*[A-Z]).{8,}$/'
                // 'name' => 'required',
                // 'username' => 'required',
                // 'email' => 'required',
                // 'password' => 'required'
            ]);

            $validatedData['password'] = bcrypt($validatedData['password']);

            User::create($validatedData);

            return response()->json(['message' => 'Sign up Success.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Sign up Failed.'], 400);
        }
    }

    // sign in
    public function signIn(Request $request)
    {
        $valid = null;

        try {
            $valid = $request->validate([
                'email' => ['required', 'email:dns'],
                'password' => ['required']
            ]);



            if (Auth::attempt($valid)) {
                return response()->json(
                    ['message' => 'Sign In Successful!'],
                    200
                );
            }
        } catch (Exception $e) {
            return response()->json(['message' => 'Sign In Failed.'], 400);
        }
    }

    // return user data for FE
    public function getUserData()
    {
        try {
            if (!Auth::check()) {
                return response()->json(null, 200);
            }

            $user = Auth::user();
            $specific = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'preferences' => $user->preferences
            ];

            return response()->json($specific, 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Get User Data Failed.'], 400);
        }
    }

    public function postUpdateUserData(Request $request, $id)
    {
        $validatedData = $request->validate([
            'new_name' => 'required',
            'new_username' => 'required',
            'new_email' => 'required',
        ]);
        $name = $validatedData['new_name'];
        $username = $validatedData['new_username'];
        $email = $validatedData['new_email'];

        $updated = User::findOrFail($id);
        $updated->name = $name;
        $updated->username = $username;
        $updated->email = $email;
        $updated->save();

        return response()->json(['message' => 'Successfully updated user profile']);
    }
}
