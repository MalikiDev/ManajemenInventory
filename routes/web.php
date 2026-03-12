<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PredictionController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin Only Routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/predictions/data/{id}', [PredictionController::class, 'predictByProduct'])->name('predictions.data');
       Route::get('/predictions', [PredictionController::class, 'index'])->name('predictions.index');
    });

    // Routes accessible by both admin and user
    Route::middleware(['permission:view-products'])->group(function () {
        Route::resource('products', ProductController::class);
    });

    Route::middleware(['permission:view-sales'])->group(function () {
        Route::resource('sales', SaleController::class);
    });

    Route::middleware(['permission:view-reports'])->group(function () {
        // Report routes will be added here
    });
});
