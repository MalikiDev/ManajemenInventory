<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PredictionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group
| which is assigned the "api" middleware group.
|
*/


// route query param: /api/predict?product_id=2
Route::get('/predict', [PredictionController::class, 'predict']);

// route path param: /api/predict/product/2
Route::get('/predict/product/{id}', [PredictionController::class, 'predictByProduct']);

// 1) route query param: /api/predict?product_id=1
Route::get('/predict', [PredictionController::class, 'predict']);

// 2) route path param: /api/predict/product/1
// this will call a small wrapper in the controller that injects product_id
Route::get('/predict/product/{id}', [PredictionController::class, 'predictByProduct']);

// keep existing user route (auth) as-is
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
