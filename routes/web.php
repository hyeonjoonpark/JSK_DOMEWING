<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ImageUploadController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\NaverShopController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Admin\CMSController;
use App\Http\Controllers\BusinessPageController;

use App\Http\Controllers\Admin\DomainController;
use App\Http\Controllers\Domewing\GeneralController;
use App\Http\Controllers\Domewing\Auth\LoginMemberController;
use App\Http\Controllers\Domewing\Auth\RegisterMemberController;
use App\Http\Controllers\Domewing\ShoppingCartController;
use App\Http\Controllers\Domewing\ProductDetailsController;
use App\Http\Controllers\Domewing\CheckoutController;
use App\Http\Controllers\Domewing\MemberController;
use App\Http\Controllers\Domewing\ToShipController;
use App\Http\Controllers\Domewing\ToReceiveController;
use App\Http\Controllers\Domewing\ToRateController;
use App\Http\Controllers\Domewing\PurchaseHistoryController;
use App\Http\Controllers\Domewing\WishlistController;
use App\Http\Controllers\Domewing\Auth\ForgetPasswordController;
use App\Http\Controllers\Domewing\Auth\ResetPasswordController;
use App\Http\Controllers\Domewing\FAQController;
use App\Http\Controllers\Namewing\NamewingController;
use App\Http\Controllers\ProductEditor\ViewController;
use App\Http\Controllers\Testmonial\TestmonialController;

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
    Route::get('product/keywords', [AdminController::class, 'productKeywords']);
    Route::post('upload-image', [ImageUploadController::class, 'handle']);
    Route::get('account-setting', [AdminController::class, 'accountSetting']);
    Route::get('product/mining', [AdminController::class, 'productMining']);
    Route::get('product/minewing', [AdminController::class, 'minewing'])->name('admin.minewing');
    Route::get('product/new-minewing', [AdminController::class, 'newMinewing']);
    Route::get('product/sold-out', [AdminController::class, 'soldOut']);
    Route::post('product/sold-out', [AdminController::class, 'soldOut']);
    Route::get('product/legacy', [AdminController::class, 'legacy']);
    Route::post('product/legacy', [AdminController::class, 'legacy']);
    Route::get('product/excelwing', [AdminController::class, 'excelwing']);
    Route::get('mappingwing/unmapped', [AdminController::class, 'unmapped']);
    Route::get('mappingwing/mapped', [AdminController::class, 'mapped']);
    Route::get('/orderwing', [AdminController::class, 'orderwing']);
    Route::get('apiwing', [AdminController::class, 'apiwing']);
    Route::get('product-editor', [ViewController::class, 'index']);
    Route::get('namewing', [NamewingController::class, 'main']);
    Route::get('edit-testmonials', [TestmonialController::class, 'index']);

    //ving kong
    Route::get('/cms_dashboard', [AdminController::class, 'cmsDashboard'])->name('admin.cms_dashboard');
    Route::get('/cms_dashboard/content_management_system/{id}', [DomainController::class, 'loadCMS']);
    Route::get('/cms/{id}', [CMSController::class, 'loadSellerCMS']);
});

Route::get('lang/{languageId}', function ($languageId) {
    // Store the selected language ID in the session or as needed
    session(['languageId' => $languageId]);

    return redirect()->back(); // Redirect to the previous page or any specific page
});

Route::middleware(['auth.members', 'translation'])->prefix('domewing')->group(function () {

    Route::get('account-settings', [MemberController::class, 'loadAccountSettings'])->name('member_details');
    Route::get('/shopping-cart', [ShoppingCartController::class, 'showShoppingCart']);
    Route::get('/checkout/{id}', [CheckoutController::class, 'showCheckoutPage']);
    Route::get('to-ship', [ToShipController::class, 'showToShip'])->name('to_ship');
    Route::get('to-receive', [ToReceiveController::class, 'showToReceive'])->name('to_receive');
    Route::get('to-rate', [ToRateController::class, 'showToRate'])->name('to_rate');
    Route::get('purchase-history', [PurchaseHistoryController::class, 'showPurchaseHistory'])->name('purchase_history');
    Route::get('wishlist', [WishlistController::class, 'showWishlist'])->name('wishlist');
    Route::get('/wishlist/search', [WishlistController::class, 'searchWishlist'])->name('search.wishlist');
});

//Index Page
Route::get('/', [BusinessPageController::class, 'showBusinessPage'])->name('business_page');

//ving kong
Route::prefix('domewing')->middleware(['translation'])->group(function () {
    Route::get('/{domain_name}', [GeneralController::class, 'loadDomain']);
    Route::get('/', [GeneralController::class, 'loadBusinessPage'])->name('domewing.home');
    Route::get('/product/{id}', [ProductDetailsController::class, 'loadProductDetail']);
    Route::post('contact-us', [GeneralController::class, 'contactUs']);
    Route::get('/products/search', [GeneralController::class, 'searchProducts'])->name('domewing.search');
    Route::get('/section/faq', [FAQController::class, 'showFAQ'])->name('domewing.FAQ');
});

Route::prefix('domewing/auth')->middleware(['translation'])->group(function () {
    Route::get('/login', [LoginMemberController::class, 'showLoginForm'])->name('domewing.auth.login');
    Route::get('/register', [RegisterMemberController::class, 'showRegisterForm']);
    Route::post('login', [LoginMemberController::class, 'login']);
    Route::get('logout', [LoginMemberController::class, 'logout']);
    Route::post('register', [RegisterMemberController::class, 'register']);
    Route::get('verify-email', [RegisterMemberController::class, 'verifyEmail'])->name('domewing.auth.verifyEmail');
    Route::get('forget-password', [ForgetPasswordController::class, 'showForgetPasswordPage'])->name('forget.password');
    Route::get('reset-password', [ResetPasswordController::class, 'showResetPasswordPage']);
    Route::post('forget-password', [ForgetPasswordController::class, 'submitRequest']);
    Route::post('reset-password', [ResetPasswordController::class, 'resetPassword']);
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

//Index for Domewing
// Route::middleware(['category', 'translation'])->group(function () {
//     Route::get('/', [GeneralController::class, 'loadBusinessPage'])->name('home');
// });

Route::get('/naver-shop/categories', [NaverShopController::class, 'getCategories']);
Route::get('/test', [TestController::class, 'handle']);
