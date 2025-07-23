<?php

use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\RecipeBookmarkController;
use App\Http\Controllers\RecipeController;

Route::get('/user', function (Request $request): User {
    return $request->user()->append('profile_url');
})->middleware('auth:sanctum');

Route::get('auth-error', function (Request $request) {
    return response()->json([
        'message' => 'Unauthorized',
    ], 401);
})->name('login');

Route::post('/register', [ApiAuthController::class, 'register']);

Route::post('/login', [ApiAuthController::class, 'login']);

Route::group(['prefix' => '/', 'middleware' => ['auth:sanctum']], function () {

    Route::get('/logout', [ApiAuthController::class, 'logout']);
    Route::post('user/profile/edit', [UserController::class, 'editProfile']);
    Route::post('user/password/change', [UserController::class, 'changePassword']);


    Route::group(['prefix' => '/recipe'], function () {


        Route::group(['prefix' => '/predict'], function () {
            Route::post('/', [RecipeController::class, 'predict']);
            Route::get('/health-check', [RecipeController::class, 'healthCheck']);
        });



        Route::group(['prefix' => '/bookmark'], function () {
            Route::post('add', [RecipeBookmarkController::class, 'addBookmark']);
            Route::post('remove', [RecipeBookmarkController::class, 'removeBookmark']);
            Route::get('list', [RecipeBookmarkController::class, 'listBookmarks']);
            Route::get('recipe/{recipeId}', [RecipeBookmarkController::class, 'getRecipeDetails']);
        });
    });


});
