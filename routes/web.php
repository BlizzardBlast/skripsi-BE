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

Route::post('/sign-in', [LoginController::class, 'sign-in']);
Route::post('/sign-up', [LoginController::class, 'sign-up']);


Route::get('/getUserData', [LoginController::class, 'getUserData']);

Route::get('/getProduct', [ProductController::class, 'getAllProduct']);
Route::get('/getProduct/sortByName', [ProductController::class, 'getSortedProductByName']);
Route::get('/getProduct/sortByPrice', [ProductController::class, 'getSortedProductByPrice']);

Route::get('/filterByBean/{bean}',[ProductController::class,'filterByBean']);


