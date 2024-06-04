<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromoController;
use Illuminate\Support\Facades\Artisan;

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
    // LOGIN
    Route::post('/sign-in', [LoginController::class, 'signIn']);
    Route::post('/sign-up', [LoginController::class, 'signUp']);
    Route::post('/sign-out', [LoginController::class, 'signOut']);

    // USER
    Route::get('/getUserData', [LoginController::class, 'getUserData']);
    Route::post('/postUpdateUserData/{id}', [LoginController::class, 'postUpdateUserData']);

    // PRODUCT
    Route::get('/getProduct', [ProductController::class, 'getAllProduct']);
    Route::get('/getProduct/sortByName', [ProductController::class, 'getSortedProductByName']);
    Route::get('/getProduct/sortByPrice', [ProductController::class, 'getSortedProductByPrice']);
    Route::get('/getProductImage/{id}', [ProductController::class, 'getProductImage']);
    Route::get('/filterByBean/{bean}', [ProductController::class, 'filterByBean']);

    // PRODUCT CONTROL
    Route::middleware(['admin'])->group(function () {
        Route::post('/addProduct', [ProductController::class, 'addProduct']);
        Route::post('/editProduct/{id}', [ProductController::class, 'editProduct']);
        Route::post('/removeProduct/{id}', [ProductController::class, 'removeProduct']);
    });

    // RECOMMENDATION
    Route::get('/getUserPref/{refresh}', [ProductController::class, 'getUserPreferences']);
    Route::post('/setUserPref', [ProductController::class, 'setUserPreferences']);

    // CART
    Route::get('/getAllUserCart', [CartController::class, 'getAllUserCart']);
    Route::post('/addToCart', [CartController::class, 'addToCart']);
    Route::post('/editQty', [CartController::class, 'editQty']);
    Route::post('/removeFromCart', [CartController::class, 'removeFromCart']);
    Route::post('/incrementQuantity', [CartController::class, 'incrementQuantity']);
    Route::post('/decrementQuantity', [CartController::class, 'decrementQuantity']);
    Route::post('/changeRoastingType', [CartController::class, 'changeRoastingType']);

    // ORDER
    Route::get('/getOrder', [OrderController::class, 'getOrder']);
    Route::get('/getOrderSpecific/{id}', [OrderController::class, 'getOrderSpecific']);
    Route::post('/postOrder', [OrderController::class, 'postOrder']);

    // PAYPAL
    Route::post('/createPayment', [PaypalController::class, 'create']);
    Route::post('/complete', [PaypalController::class, 'complete']);

    // PROMO
    Route::post('/checkPromo', [PromoController::class, 'checkPromo']);
    Route::get('/getAllPromo', [PromoController::class, 'getAllPromo']);

    // PROMO CONTROL
    Route::middleware(['admin'])->group(function () {
        Route::post('/postPromo', [PromoController::class, 'postPromo']);
        Route::post('/deletePromo/{id}', [PromoController::class, 'deletePromo']);
    });

    Route::get('/link-storage', function () {
        try {
            if (!file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });
});
