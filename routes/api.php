<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\FundManagerController;
use App\Http\Controllers\DuplicateWarningController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* |-------------------------------------------------------------------------- | API Routes |-------------------------------------------------------------------------- | | Here is where you can register API routes for your application. These | routes are loaded by the RouteServiceProvider within a group which | is assigned the "api" middleware group. Enjoy building your API! | */

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Fund API endpoints
Route::get('/funds', [FundController::class , 'index']);
Route::post('/funds', [FundController::class , 'store']);
Route::get('/funds/{id}', [FundController::class , 'show']);
Route::put('/funds/{id}', [FundController::class , 'update']);
Route::delete('/funds/{id}', [FundController::class , 'destroy']);

// Fund Manager API endpoints
Route::get('/fund-managers', [FundManagerController::class , 'index']);
Route::post('/fund-managers', [FundManagerController::class , 'store']);
Route::delete('/fund-managers/{id}', [FundManagerController::class , 'destroy']);

// Duplicate Warning API endpoints
Route::get('/duplicate-warnings', [DuplicateWarningController::class , 'index']);

// Company API endpoints
Route::get('/companies', [CompanyController::class , 'index']);
Route::post('/companies', [CompanyController::class , 'store']);
Route::delete('/companies/{id}', [CompanyController::class , 'destroy']);
