<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/getUserData', function (Request $request) {
//     return $request->user();
// });

Route::post('/sign-in', [LoginController::class, 'signIn']);
Route::post('/sign-up', [LoginController::class, 'signUp']);
Route::post('/sign-out', [LoginController::class, 'signOut']);

Route::get('/getUserData', [LoginController::class, 'getUserData']);

Route::get('/getProduct', [ProductController::class, 'getAllProduct']);
Route::get('/getProduct/sortByName', [ProductController::class, 'getSortedProductByName']);
Route::get('/getProduct/sortByPrice', [ProductController::class, 'getSortedProductByPrice']);

Route::get('/filterByBean/{bean}', [ProductController::class, 'filterByBean']);
