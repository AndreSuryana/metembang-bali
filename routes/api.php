<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\TembangController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\TembangSubmissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserSubmissionController;
use Illuminate\Support\Facades\Route;

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

Route::group([
    'prefix' => 'v1',
], function () {

    /**
     * Users Routes :
     */
    Route::prefix('user')->group(function () {
        /**
         * User Authentication Routes :
         */
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout'])
            ->middleware('auth:sanctum');

        /**
         * Authenticated User Routes : (login as user required)
         */
        Route::middleware('auth:sanctum')->group(function () {
            /**
             * Manage User Routes :
             */
            Route::get('/', [UserController::class, 'fetchUser']);
            Route::put('/', [UserController::class, 'updateUser']);
            Route::post('photo', [UserController::class, 'uploadPhoto']);
            Route::post('password', [UserController::class, 'changePassword']);

            /**
             * User Submission Routes :
             */
            Route::get('submission', [UserSubmissionController::class, 'index']);
            Route::post('submission', [UserSubmissionController::class, 'store']);
            Route::delete('submission/{id}/delete', [UserSubmissionController::class, 'destroy']);
        });
    });

    /**
     * Admin Routes : (login as admin required)
     */
    Route::prefix('admin')->group(function () {
        /**
         * Tembang Submission Routes :
         */
        Route::get('submission', [TembangSubmissionController::class, 'index']);
        Route::get('submission/{id}', [TembangSubmissionController::class, 'show']);
        Route::post('submission/{id}/accept', [TembangSubmissionController::class, 'accept']);
        Route::post('submission/{id}/reject', [TembangSubmissionController::class, 'reject']);
    });

    /**
     * Tembang Routes :
     */
    Route::prefix('tembang')->group(function () {
        /**
         * Data Tembang Routes :
         */
        Route::get('/', [TembangController::class, 'index']);
        Route::get('latest', [TembangController::class, 'latest']);
        Route::get('top', [TembangController::class, 'top']);
        Route::get('{tembangUID}/detail', [TembangController::class, 'show']);

        /**
         * Category Routes :
         */
        Route::get('category', [CategoryController::class, 'index']);
        Route::get('sub-category', [CategoryController::class, 'subCategoryIndex']);

        /**
         * Filter Data Routes :
         */
        Route::get('filter/rule', [FilterController::class, 'rules']);
        Route::get('filter/usage', [FilterController::class, 'usages']);
        Route::get('filter/usage/type', [FilterController::class, 'usageTypes']);
        Route::get('filter/mood', [FilterController::class, 'moods']);

        /**
         * Other Routes :
         */
        Route::get('random', [TembangController::class, 'random']);
    });
});
