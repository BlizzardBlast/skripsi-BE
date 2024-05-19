<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
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
    Route::post('/postUpdateUserData/{id}', [LoginController::class, 'postUpdateUserData']);

    Route::get('/getProduct', [ProductController::class, 'getAllProduct']);
    Route::get('/getProduct/sortByName', [ProductController::class, 'getSortedProductByName']);
    Route::get('/getProduct/sortByPrice', [ProductController::class, 'getSortedProductByPrice']);
    Route::get('/getProductImage/{id}', [ProductController::class, 'getProductImage']);

    Route::get('/getUserPref', [ProductController::class, 'getUserPreferences']);
    Route::post('/setUserPref', [ProductController::class, 'setUserPreferences']);

    Route::get('/filterByBean/{bean}', [ProductController::class, 'filterByBean']);

    Route::post('/addProduct', [ProductController::class, 'addProduct']);
    Route::post('/editProduct/{id}', [ProductController::class, 'editProduct']);
    Route::post('/removeProduct/{id}', [ProductController::class, 'removeProduct']);



    // CART
    Route::get('/getAllUserCart', [CartController::class, 'getAllUserCart']);
    Route::post('/addToCart', [CartController::class, 'addToCart']);
    Route::post('/editQty', [CartController::class, 'editQty']);
    Route::post('/removeFromCart', [CartController::class, 'removeFromCart']);

    Route::post('/incrementQuantity', [CartController::class, 'incrementQuantity']);
    Route::post('/decrementQuantity', [CartController::class, 'decrementQuantity']);

    // Order
    Route::get('/getOrder', [OrderController::class, 'getOrder']);
    Route::get('/getOrderSpecific/{id}', [OrderController::class, 'getOrderSpecific']);
    Route::post('/postOrder', [OrderController::class, 'postOrder']);

    //PAYPAL
    Route::post('/createPayment', [PaypalController::class, 'create']);
    Route::post('/complete', [PaypalController::class, 'complete']);
});
