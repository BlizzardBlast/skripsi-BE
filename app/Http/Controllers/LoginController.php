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
use Illuminate\Validation\ValidationException;

class LoginController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // sign up
    // password: min 8, 1 lower, 1 upper, 1 digit

    public function signOut(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'No user is currently signed in.'], 401);
        }

        Auth::guard(name: 'web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Signed Out.'], 200);
    }

    public function signUp(Request $request)
    {
        $response = ['message' => 'Sign up failed.', 'status' => 400];

        if (Auth::check()) {
            $response = ['message' => 'You are already signed in.', 'status' => 403];
        } else {
            try {
                $validatedData = $request->validate([
                    'name' => 'required|min:3|max:255',
                    'username' => 'required|min:3|max:255',
                    'email' => 'required|email:dns|unique:users',
                    'password' => 'required|min:8|max:255|regex:/^(?=.*[a-z])(?=.*[A-Z]).{8,}$/'
                ]);

                $validatedData['password'] = bcrypt($validatedData['password']);

                User::create($validatedData);

                $response = ['message' => 'Sign up Success.', 'status' => 200];
            } catch (ValidationException $e) {
                $response = ['message' => $e->getMessage(), 'status' => $e->status];
            } catch (Exception $e) {
                $response = ['message' => 'Sign up failed.', 'status' => 400];
            }
        }

        return response()->json(['message' => $response['message']], $response['status']);
    }

    // sign in
    public function signIn(Request $request)
    {
        $response = ['message' => 'Sign In Failed.', 'status' => 400];

        if (Auth::check()) {
            $response = ['message' => 'Already signed in.', 'status' => 400];
        } else {
            try {
                $credentials = $request->validate([
                    'email' => ['required', 'email:dns'],
                    'password' => ['required']
                ]);

                if (Auth::attempt($credentials)) {
                    $response = ['message' => 'Sign In Successful!', 'status' => 200];
                } else {
                    $response = ['message' => 'Invalid credentials.', 'status' => 400];
                }
            } catch (ValidationException $e) {
                $response = ['message' => $e->getMessage(), 'status' => 400];
            } catch (Exception $e) {
                $response = ['message' => 'Sign In Failed.', 'status' => 400];
            }
        }

        return response()->json(['message' => $response['message']], $response['status']);
    }

    // return user data for FE
    public function getUserData()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'No user is currently logged in.'], 401);
        }

        try {
            $user = Auth::user();
            $specific = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role,
                'preferences' => $user->preferences
            ];

            return response()->json($specific, 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Get User Data Failed.'], 500);
        }
    }

    public function postUpdateUserData(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(null, 400);
        }

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
