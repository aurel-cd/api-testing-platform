<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/test', function (){
    dd(2);
})->middleware('auth');

Route::prefix('auth')->name('auth.')->group(function(){
   Route::post('login', [AuthController::class, 'login'])->name('login');
   Route::post('register', [AuthController::class, 'register'])->name('register');
   Route::post('verify-email', [AuthController::class, 'verifyEmail'])->name('verify-email')->middleware("auth");
   Route::get('/user', [AuthController::class, 'user'])->name('user')->middleware("auth:sanctum");
});

Route::middleware('auth:sanctum')->group(function (){
    /**
     * Here go the protected routes
     */
});
