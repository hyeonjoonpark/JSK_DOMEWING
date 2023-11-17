<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ImageUploadController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NaverShopController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Admin\CMSController;

use App\Http\Controllers\Admin\DomainController;
use App\Http\Controllers\Domewing\GeneralController;
use App\Http\Controllers\Domewing\DomewingLoginController;
use App\Http\Controllers\Domewing\DomewingRegisterController;

// 관리자 콘솔 라우트 그룹 설정
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/dashboard', [AdminController::class, 'dashboard']); // 대시보드 페이지는 뷰로 직접 로드
    Route::post('submit-post', [DashboardController::class, 'createPost']);
    Route::get('product/search', [AdminController::class, 'productSearch']);
    Route::get('product/search-to-register', [AdminController::class, 'searchToRegister']);
    Route::get('product/register', [AdminController::class, 'productRegister']);
    Route::get('product/manage', [AdminController::class, 'productManage']);
    Route::get('product/uploaded', [AdminController::class, 'uploadedProducts']);
    Route::post('upload-image', [ImageUploadController::class, 'handle']);
    Route::get('account-setting', [AdminController::class, 'accountSetting']);

    //ving kong
    Route::get('/cms_dashboard', [AdminController::class, 'cmsDashboard'])->name('admin.cms_dashboard');
    Route::get('/cms_dashboard/content_management_system/{id}', [CMSController::class, 'loadCMS']);

});

Route::middle(['auth.members'])->prefix('domewing')->group(function () {
    Route::get('/account-setting', []);
});

//ving kong
Route::prefix('domewing')->group(function () {
    Route::get('/{domain_name}', [GeneralController::class, 'loadDomain']);
    Route::get('/', [GeneralController::class, 'loadBusinessPage']);
});

Route::prefix('domewing/auth')->group(function () {
    Route::get('/login', [DomewingLoginController::class, 'login']);
    Route::get('/register', [DomewingRegisterController::class, 'register']);
    Route::post('login', [DomewingLoginController::class, 'login']);
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
Route::get('/naver-shop/categories', [NaverShopController::class, 'getCategories']);
Route::get('/test', [TestController::class, 'handle']);
