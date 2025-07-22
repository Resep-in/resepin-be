<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ApiAuthController;

Route::get('/user', function (Request $request): User {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('auth-error', function (Request $request) {
    return response()->json([
        'message' => 'Unauthorized',
    ], 401);
})->name('login');

Route::post('/login', [ApiAuthController::class, 'login']);

Route::group(['prefix' => '/', 'middleware' => ['auth:sanctum']], function () {
    Route::get('/logout', [ApiAuthController::class, 'logout']);
});
