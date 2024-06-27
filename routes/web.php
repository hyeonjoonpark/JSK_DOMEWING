<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ImageUploadController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\CMSController;
use App\Http\Controllers\BusinessPageController;
use App\Http\Controllers\Admin\DomainController;
use App\Http\Controllers\Admin\PartnersManagementController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\Namewing\NamewingController;
use App\Http\Controllers\OpenMarkets\AccountManagementController;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangOrderController;
use App\Http\Controllers\OpenMarkets\OpenMarketOrderController;
use App\Http\Controllers\Partners\PartnerAccountSetting;
use App\Http\Controllers\Partners\DashboardController as PartnersDashboardController;
use App\Http\Controllers\Partners\ForgotPasswordController;
use App\Http\Controllers\Partners\LoginController as PartnersLoginController;
use App\Http\Controllers\Partners\PartnerController;
use App\Http\Controllers\Partners\Products\CollectController;
use App\Http\Controllers\Partners\Products\ManageController;
use App\Http\Controllers\Partners\Products\UploadController;
use App\Http\Controllers\Partners\Products\UploadedController;
use App\Http\Controllers\Partners\RegisterController as PartnersRegisterController;
use App\Http\Controllers\ProductEditor\ViewController;
use App\Http\Controllers\SmartStore\SmartStoreOrderController;
use App\Http\Controllers\Testmonial\TestmonialController;

// 셀윙 파트너스.
Route::prefix('partner')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('login', [PartnersLoginController::class, 'index'])->name('partner.login');
        Route::post('login', [PartnersLoginController::class, 'login']);
        Route::get('register', [PartnersRegisterController::class, 'index'])->name('partner.register');
        Route::post('register', [PartnersRegisterController::class, 'main']);
        Route::get('forgot-password', [ForgotPasswordController::class, 'index']);
        Route::post('forgot-password', [ForgotPasswordController::class, 'main']);
        Route::get('logout', [PartnersLoginController::class, 'logout']);
    });
    Route::middleware(['auth.partner'])->group(function () {
        Route::get('/dashboard', [PartnersDashboardController::class, 'index'])->name('partner.dashboard');
        Route::get('/', [PartnersDashboardController::class, 'index']);
        Route::get('/index', [PartnersDashboardController::class, 'index']);
        Route::prefix('account-setting')->group(function () {
            Route::get('/partner', [PartnerAccountSetting::class, 'index']);
            Route::get('/open-market', function () {
                return view('partner/account_setting_open_market');
            });
            Route::get('/accounts-management', [AccountManagementController::class, 'index']);
            Route::get('/dowewing-integration', [AccountManagementController::class, 'domewing']);
        });
        Route::prefix('products')->group(function () {
            Route::get('collect', [CollectController::class, 'index'])->name('partner.products.collect');
            Route::get('manage', [ManageController::class, 'index'])->name('partner.products.manage');
            Route::get('upload', [UploadController::class, 'index']);
            Route::get('sale', [UploadedController::class, 'index'])->name('partner.products.uploaded');
        });
        Route::get('open-market', [PartnerController::class, 'partnerOpenMarket']);
        Route::get('excelwing-download', [PartnerController::class, 'excelwing']);
        Route::get('excelwing-upload', [PartnerController::class, 'excelUploadIndex']);
    });
});
// 셀윙 관리자 패널.
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/partners', [PartnersManagementController::class, 'index']);
    Route::get('/dashboard', [AdminDashboardController::class, 'index']); // 대시보드 페이지는 뷰로 직접 로드
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
    Route::post('product/minewing', [AdminController::class, 'searchProductCodes']);
    Route::get('product/new-minewing', [AdminController::class, 'newMinewing']);
    Route::get('product/sold-out', [AdminController::class, 'soldOut'])->name('admin.product.sold-out');
    Route::post('product/sold-out', [AdminController::class, 'searchSoldOutCodes']);
    Route::get('product/legacy', [AdminController::class, 'legacy']);
    Route::post('product/legacy', [AdminController::class, 'legacy']);
    Route::get('product/excelwing', [AdminController::class, 'excelwing']);
    Route::get('mappingwing/unmapped', [AdminController::class, 'unmapped']);
    Route::get('mappingwing/mapped', [AdminController::class, 'mapped']);
    Route::get('/orderwing', [AdminController::class, 'orderwing']);
    Route::get('open-market', [AdminController::class, 'openMarket']);
    Route::get('get-new-orders', [OpenMarketOrderController::class, 'index']);
    Route::get('apiwing', [AdminController::class, 'apiwing']);
    Route::get('product-editor', [ViewController::class, 'index']);
    Route::get('namewing', [NamewingController::class, 'main']);
    Route::get('edit-testmonials', [TestmonialController::class, 'index']);
    Route::get('/cms_dashboard', [AdminController::class, 'cmsDashboard'])->name('admin.cms_dashboard');
    Route::get('/cms_dashboard/content_management_system/{id}', [DomainController::class, 'loadCMS']);
    Route::get('/cms/{id}', [CMSController::class, 'loadSellerCMS']);
    Route::get('contact-us', [ContactUsController::class, 'index']);
});
// ?? 뭐야 씨발
Route::get('lang/{languageId}', function ($languageId) {
    session(['languageId' => $languageId]);
    return redirect()->back();
});
// 셀윙 홈페이지
Route::get('/', [BusinessPageController::class, 'showBusinessPage'])->name('business_page');
// 셀윙 관리자 로그인.
Route::prefix('auth')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('auth.login');
    Route::get('login', [LoginController::class, 'showLoginForm']);
    Route::get('register', [RegisterController::class, 'index'])->name('auth.register');
    Route::post('create-user', [RegisterController::class, 'createUser'])->name('createUser');
    Route::get('verify-email', [RegisterController::class, 'verifyEmail'])->name('auth.verifyEmail');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('logout', [LoginController::class, 'logout']);
});
