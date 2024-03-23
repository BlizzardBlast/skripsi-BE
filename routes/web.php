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

Route::post('/api/sign-in', [LoginController::class, 'signIn']);
Route::post('/api/sign-up', [LoginController::class, 'signUp']);
Route::post('/api/sign-out', [LoginController::class, 'signOut']);

Route::get('/api/getUserData', [LoginController::class, 'getUserData']);

Route::get('/api/getProduct', [ProductController::class, 'getAllProduct']);
Route::get('/api/getProduct/sortByName', [ProductController::class, 'getSortedProductByName']);
Route::get('/api/getProduct/sortByPrice', [ProductController::class, 'getSortedProductByPrice']);

Route::get('/api/filterByBean/{bean}', [ProductController::class, 'filterByBean']);
