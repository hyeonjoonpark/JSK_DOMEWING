<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;

// 관리자 콘솔 라우트 그룹 설정
Route::middleware('auth')
    ->prefix('admin')
    ->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::post('submit-post', [DashboardController::class, 'createPost']);
    });

// 로그인 및 등록 라우트
Route::prefix('auth')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('auth.login');
    Route::get('login', [LoginController::class, 'showLoginForm']);
    Route::get('register', [RegisterController::class, 'index'])->name('auth.register');
    Route::post('create-user', [RegisterController::class, 'createUser'])->name('createUser');
    Route::get('verify-email', [RegisterController::class, 'verifyEmail'])->name('auth.verifyEmail');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('logout', [LoginController::class, 'logout']);
});

Route::get('/', [HomeController::class, 'index'])->name('home');