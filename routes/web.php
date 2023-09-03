<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Middleware\Authenticate;

// 관리자 콘솔 라우트 그룹 설정
Route::middleware([Authenticate::class, 'redirectTo'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/', [AdminController::class, 'index']);
    });

// 로그인 및 등록 라우트
Route::group(['prefix' => 'auth'], function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('auth.login');
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('auth.login');
    Route::get('register', [RegisterController::class, 'register']);
});
Route::group(['prefix' => '/'], function () {
    Route::get('/', [HomeController::class, 'index']);
});