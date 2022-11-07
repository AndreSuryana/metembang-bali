<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\TembangSubmissionController;
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

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::prefix('admin')->group(function () {
    /**
     * Admin Authentication Routes :
     */
    Route::get('login', [AdminController::class, 'login'])
        ->name('admin.login')->middleware('guest.admin');
    Route::post('login', [AdminController::class, 'authenticate'])
        ->name('admin.authenticate')->middleware('guest.admin');
    Route::post('logout', [AdminController::class, 'logout'])
        ->name('admin.logout')->middleware('auth.admin');

    /**
     * Admin Menu Routes :
     */
    Route::get('dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard')->middleware('auth.admin');
    Route::get('submission', [AdminController::class, 'submission'])
        ->name('admin.submission')->middleware('auth.admin');

    /**
     * Submission Menu Routes :
     */
    Route::get('submission/{id}/detail', [AdminController::class, 'showSubmission'])
        ->name('admin.submission.detail')->middleware('auth.admin');
    Route::post('submission/{id}/delete', [AdminController::class, 'destroySubmission'])
        ->name('admin.submission.destroy')->middleware('auth.admin');
    Route::post('submission/{id}/accept', [TembangSubmissionController::class, 'accept'])
        ->name('admin.submission.accept')->middleware('auth.admin');
    Route::post('submission/{id}/reject', [TembangSubmissionController::class, 'reject'])
        ->name('admin.submission.reject')->middleware('auth.admin');
});
