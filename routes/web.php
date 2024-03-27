<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::prefix('/api')->group(function () {
    Route::post('/sign-in', [LoginController::class, 'signIn']);
    Route::post('/sign-up', [LoginController::class, 'signUp']);
    Route::post('/sign-out', [LoginController::class, 'signOut']);

    Route::get('/getUserData', [LoginController::class, 'getUserData']);

    Route::get('/getProduct', [ProductController::class, 'getAllProduct']);
    Route::get('/getProduct/sortByName', [ProductController::class, 'getSortedProductByName']);
    Route::get('/getProduct/sortByPrice', [ProductController::class, 'getSortedProductByPrice']);
    Route::get('/getProductImage/{id}', [ProductController::class, 'getProductImage']);

    Route::get('/filterByBean/{bean}', [ProductController::class, 'filterByBean']);
});
