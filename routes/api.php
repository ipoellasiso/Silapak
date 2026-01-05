<?php

use App\Http\Controllers\Sp2dApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Data SP2D
Route::apiResource('/sp2d_api', Sp2dApiController::class);
Route::apiResource('/rek_belanja_api', Sp2dApiController::class);
Route::apiResource('/pajak_potongan_api', Sp2dApiController::class);
