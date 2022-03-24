<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductCategoryController;
use App\Http\Controllers\API\TransactionController;

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


// membuat route untuk menampilkan url api product
Route::get('products', [ProductController::class, 'all']);
// membuat route untuk menampilkan url api category product
Route::get('categories', [ProductCategoryController::class, 'all']);

// membuat route untuk menampilkan API untuk register 
Route::post('register', [UserController::class, 'register']);
// membuat route untuk menampilkan API untuk login 
Route::post('login', [UserController::class, 'login']);

// middleware untuk mengecek apakah sudah login atau belum 
Route::middleware('auth:sanctum')->group(function () {
    // membuat route untuk mengambil data user 
    Route::get('user', [UserController::class, 'fetch']);
    // membuat route untuk update data user 
    Route::post('update', [UserController::class, 'updateProfil']);
    // membuat route untuk logout 
    Route::post('logout', [UserController::class, 'logout']);
    // membuat route untuk mengambil data transaction 
    Route::get('transaction', [TransactionController::class, 'all']);
    // membuat route untuk mengambil data checkou untuk barang pembelian  
    Route::post('checkout', [TransactionController::class, 'checkout']);
});