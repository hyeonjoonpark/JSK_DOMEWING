<?php

use App\Http\Controllers\Admin\AccountSettingController;
use App\Http\Controllers\Admin\CMSController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DomainController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\PartnersManagementController;
use App\Http\Controllers\Admin\ProductCollectController;
use App\Http\Controllers\Admin\ProductDataValidityController;
use App\Http\Controllers\Admin\ProductDetailController;
use App\Http\Controllers\Admin\ProductKeywordController;
use App\Http\Controllers\Admin\ProductRegisterController;
use App\Http\Controllers\Admin\ProductSearchController;
use App\Http\Controllers\Admin\ShippingFeeController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\APIwing\IndexController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Domewing\ProductDetailsController;
use App\Http\Controllers\Domewing\ShoppingCartController;
use App\Http\Controllers\Domewing\OrderController;
use App\Http\Controllers\Domewing\CheckoutController;
use App\Http\Controllers\Domewing\MemberController;
use App\Http\Controllers\Domewing\ToReceiveController;
use App\Http\Controllers\Domewing\ToRateController;
use App\Http\Controllers\Mappingwing\CategorySearchController;
use App\Http\Controllers\Mappingwing\RequestMappingController;
use App\Http\Controllers\Mappingwing\SelectCategoryController;
use App\Http\Controllers\Minewing\ManufactureController as MinewingManufactureController;
use App\Http\Controllers\Minewing\SaveController;
use App\Http\Controllers\Minewing\UniqueProductHrefsController;
use App\Http\Controllers\Namewing\EditProductNameController;
use App\Http\Controllers\NewMinewing\MiningController as NewMinewingMiningController;
use App\Http\Controllers\Orderwing\CollectOrderController;
use App\Http\Controllers\Product\CategoryMappingController;
use App\Http\Controllers\Product\ExcelwingController;
use App\Http\Controllers\Product\FilterDuplicatesController;
use App\Http\Controllers\Product\GetProductController;
use App\Http\Controllers\Product\InsertController;
use App\Http\Controllers\Product\ManufactureController;
use App\Http\Controllers\Product\MiningController;
use App\Http\Controllers\Product\ProcessController;
use App\Http\Controllers\Product\ValidateProductNamesController;
use App\Http\Controllers\Productwing\RestockController;
use App\Http\Controllers\Productwing\SoldOutController;

use App\Http\Controllers\BusinessPageController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\Namewing\NamewingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangController;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangOrderController;
use App\Http\Controllers\OpenMarkets\KakaoShopping\KakaoShoppingAccountController;
use App\Http\Controllers\OpenMarkets\LotteOn\LotteOnAccountController;
use App\Http\Controllers\OpenMarkets\LotteOn\LotteOnApiController;
use App\Http\Controllers\OpenMarkets\OpenMarketExchangeController;
use App\Http\Controllers\OpenMarkets\OpenMarketOrderController;
use App\Http\Controllers\OpenMarkets\OpenMarketRefundController;
use App\Http\Controllers\OpenMarkets\OpenMarketShipmentController;
use App\Http\Controllers\OpenMarkets\St11\AccountController;
use App\Http\Controllers\OpenMarkets\St11\St11OrderController;
use App\Http\Controllers\OpenMarkets\TMon\TMonAccountController;
use App\Http\Controllers\Partners\PartnerAccountSetting;
use App\Http\Controllers\Partners\ExcelwingController as PartnerExcelwingController;

use App\Http\Controllers\Partners\Products\ManageController;
use App\Http\Controllers\Partners\Products\PartnerTableController;
use App\Http\Controllers\Partners\Products\UploadController;
use App\Http\Controllers\Partners\Products\UploadedController;
use App\Http\Controllers\Partners\Products\ViewController;
use App\Http\Controllers\Product\DownloadController;
use App\Http\Controllers\ProductEditor\ExcelwingController as ProductEditorExcelwingController;
use App\Http\Controllers\ProductEditor\IndexController as ProductEditorIndexController;
use App\Http\Controllers\ProductEditor\MainController;
use App\Http\Controllers\SellwingApis\AuthController;
use App\Http\Controllers\SellwingApis\CategoryListController;
use App\Http\Controllers\SellwingApis\VendorListController;
use App\Http\Controllers\SmartStore\SmartStoreAccountController;
use App\Http\Controllers\SmartStore\SmartStoreExchangeController;
use App\Http\Controllers\SmartStore\SmartStoreOrderController;
use App\Http\Controllers\SmartStore\SmartstoreProductUpload;
use App\Http\Controllers\SmartStore\SmartStoreReturnController;
use App\Http\Controllers\SmartStore\SmartStoreShipmentController;
use App\Http\Controllers\Testmonial\TestmonialController;
use App\Http\Controllers\TrackSoldOutController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware(['auth.custom'])->group(function () {
    Route::post('/webhook', [WebhookController::class, 'webhook']);
    Route::post('/set-post-confirmed', [DashboardController::class, 'setPostConfirmed']);
    Route::post('/delete-post', [DashboardController::class, 'deletePost']);
    Route::post('/product/search', [ProductSearchController::class, 'index']);
    Route::post('/product/category', [ProductRegisterController::class, 'categorySearch']);
    Route::post('/product/register', [ProductRegisterController::class, 'handle']);
    Route::post('/product/upload', [FormController::class, 'index']);
    Route::post('/product/keywords', [ProductKeywordController::class, 'index']);
    Route::post('/product/data-validity', [ProductDataValidityController::class, 'index']);
    Route::post('/product/mining', [MiningController::class, 'index']);
    Route::post('/product/process', [ProcessController::class, 'index']);
    Route::post('/product/unique', [FilterDuplicatesController::class, 'index']);
    Route::post('/product/manufacture', [ManufactureController::class, 'index']);
    Route::post('/product/validate-product-names', [ValidateProductNamesController::class, 'index']);
    Route::post('/product/insert', [InsertController::class, 'index']);
    Route::post('/product/get-product', [GetProductController::class, 'index']);
    Route::post('/product/excelwing', [ExcelwingController::class, 'index']);
    Route::post('/product/category-mapping', [CategoryMappingController::class, 'index']);
    Route::post('/product/sold-out', [SoldOutController::class, 'index']);
    Route::post('/product/restock', [RestockController::class, 'index']);
    Route::post('/product/new-minewing', [NewMinewingMiningController::class, 'index']);
    Route::post('/product/edit-name', [EditProductNameController::class, 'index']);
    Route::post('/product/edit-name', [EditProductNameController::class, 'index']);
    Route::post('/product/download', [DownloadController::class, 'main']);
    Route::post('/product/edit', [MainController::class, 'main']);
    Route::post('/product/edit/excelwing', [ProductEditorExcelwingController::class, 'index']);
    Route::post('/product/view', [ViewController::class, 'main']);
    Route::post('/namewing/power-namewing', [NamewingController::class, 'power']);
    Route::post('/namewing/multi-edit', [NamewingController::class, 'multiEdit']);
    // account-setting
    Route::prefix('account-setting')->group(function () {
        Route::post('margin-rate', [AccountSettingController::class, 'changeMarginRate']);
        Route::post('shipping-fee', [ShippingFeeController::class, 'index']);
        Route::put('update-commission', [AccountSettingController::class, 'updateVendorCommission']);
    });
    // minewing
    Route::post('/minewing/unique-product-hrefs', [UniqueProductHrefsController::class, 'index']);
    Route::post('/minewing/manufacture', [MinewingManufactureController::class, 'index']);
    Route::post('/minewing/save-products', [SaveController::class, 'index']);
    // orderwing
    Route::post('/orderwing', [CollectOrderController::class, 'index']);
    // Load product details
    Route::post('/product/load-product-detail', [ProductDetailController::class, 'index']);
    Route::post('/product/collect', [ProductCollectController::class, 'index']);
    Route::post('/product/load-bulk-details', [ProductDetailController::class, 'bulk']);
    Route::post('/product/insert-bulk-products', [ProductCollectController::class, 'bulk']);
    Route::post('/mappingwing/select-category', [SelectCategoryController::class, 'request']);
    Route::post('/mappingwing/category-search', [CategorySearchController::class, 'index']);
    Route::post('/mappingwing/request-mapping', [RequestMappingController::class, 'index']);
    Route::post('/mappingwing/get-mapped', [SelectCategoryController::class, 'mappedRequest']);
    // Testmonials
    Route::post('/testmonials/add', [TestmonialController::class, 'add']);
    Route::post('/testmonials/del', [TestmonialController::class, 'del']);
    Route::post('/testmonials/edt', [TestmonialController::class, 'edt']);
    //
    Route::post('/admin/remove-domain', [DomainController::class, 'removeDomain']);
    Route::get('/admin/get-domain', [DomainController::class, 'getDomain']);
    Route::post('admin/edit-domain', [DomainController::class, 'editDomain']);
    Route::post('admin/upload-image-banner', [DomainController::class, 'uploadImageBanner']);
    Route::post('admin/change-image-status', [DomainController::class, 'changeImageStatus']);
    Route::post('admin/remove-image-banner', [DomainController::class, 'removeImage']);
    Route::post('admin/change-theme-color', [DomainController::class, 'changeThemeColor']);
    Route::post('seller/edit-domain-name', [CMSController::class, 'editDomainName']);
    Route::post('seller/upload-image-banner', [CMSController::class, 'uploadImageBanner']);
    Route::post('seller/change-image-status', [CMSController::class, 'changeImageStatus']);
    Route::post('seller/remove-image-banner', [CMSController::class, 'removeImage']);
    Route::post('seller/change-theme-color', [CMSController::class, 'changeThemeColor']);

    Route::post('member/add-to-cart', [ProductDetailsController::class, 'addToCart']);
    Route::post('member/remove-all-cart', [ProductDetailsController::class, 'removeAllCartItem']);
    Route::post('member/add-to-wishlist', [ProductDetailsController::class, 'addToWishlist']);
    Route::post('member/remove-cart-item', [ShoppingCartController::class, 'removeCartItem']);
    Route::post('member/update-quantity', [ShoppingCartController::class, 'updateQuantity']);
    Route::post('member/create-order', [OrderController::class, 'createOrder']);
    Route::post('member/create-single-order', [OrderController::class, 'createSingleOrder']);
    Route::post('member/checkout-order', [CheckoutController::class, 'checkoutOrder']);

    Route::post('member/update-profile', [MemberController::class, 'updateProfile']);
    Route::get('member/get-transaction-details/{id}', [MemberController::class, 'getTransactionDetails']);
    Route::post('member/order-received', [ToReceiveController::class, 'confirmReceived']);
    Route::post('member/submit-review', [ToRateController::class, 'submitReview']);
    Route::post('member/edit-review', [ToRateController::class, 'editReview']);

    Route::prefix('partner')->group(function () {
        Route::post('update-is-active', [PartnersManagementController::class, 'updateIsActive']);
        Route::post('update-type', [PartnersManagementController::class, 'updateType']);
    });

    Route::post('apiwing/get-unset-categories', [IndexController::class, 'getUnsetCategories']);
    Route::prefix('sellwing-api')->group(function () {
        Route::post('vendor-list', [VendorListController::class, 'main']);
        Route::post('category-list', [CategoryListController::class, 'index']);
    });
    //주문처리
    Route::post('process-order', [OpenMarketOrderController::class, 'processOrder']);
    //메모작성
    Route::post('set-memo', [OpenMarketOrderController::class, 'setMemo']);
    // 파트너스 주문 조회
    Route::post('get-new-orders', [OpenMarketOrderController::class, 'index']);
    Route::post('show-data', [OpenMarketOrderController::class, 'showData']);
    Route::post('getOrderInfo', [OpenMarketOrderController::class, 'getOrderInfo']);

    Route::post('contact-us/update', [ContactUsController::class, 'update']);

    Route::prefix('admin/dashboard')->group(function () {
        Route::post('weeklySales', [AdminDashboardController::class, 'getWeeklySales']);
        Route::post('action-center', [AdminDashboardController::class, 'getActionCenter']);
        Route::post('top-6-member-sales', [AdminDashboardController::class, 'getTop6MemberSales']);
        Route::get('top-vendors', [AdminDashboardController::class, 'getTopVendors']);
    });

    Route::post('trackwing', [TrackSoldOutController::class, 'main'])->name('trackwing');
});

//테스트
// Route::post('test', [St11OrderController::class, 'index']);
Route::post('test', [LotteOnApiController::class, 'index']);
Route::post('smart-store-return-test', [SmartStoreReturnController::class, 'index']);
Route::post('smart-store-exchange-test', [SmartStoreExchangeController::class, 'index']);



Route::prefix('partner')->middleware('auth.partner.api')->group(function () {
    Route::prefix('account-setting')->group(function () {
        Route::post('partner', [PartnerAccountSetting::class, 'validatePartner']);
        Route::post('list', [PartnerAccountSetting::class, 'list']);
        Route::prefix('coupang')->group(function () {
            Route::post('/', [CoupangController::class, 'accountSetting']);
            Route::post('edit', [CoupangController::class, 'edit']);
            Route::post('delete', [CoupangController::class, 'delete']);
        });
        Route::prefix('smart-store')->group(function () {
            Route::post('/', [SmartStoreAccountController::class, 'add']);
            Route::post('edit', [SmartStoreAccountController::class, 'edit']);
            Route::post('delete', [SmartStoreAccountController::class, 'delete']);
        });
        Route::prefix('st11')->group(function () {
            Route::post('/', [AccountController::class, 'add']);
            Route::post('edit', [AccountController::class, 'edit']);
            Route::post('delete', [AccountController::class, 'delete']);
        });
        Route::prefix('lotte-on')->group(function () {
            Route::post('/', [LotteOnAccountController::class, 'add']);
            Route::post('edit', [LotteOnAccountController::class, 'edit']);
            Route::post('delete', [LotteOnAccountController::class, 'delete']);
        });
        Route::prefix('kakao-shopping')->group(function () {
            Route::post('/', [KakaoShoppingAccountController::class, 'add']);
            Route::post('edit', [KakaoShoppingAccountController::class, 'edit']);
            Route::post('delete', [KakaoShoppingAccountController::class, 'delete']);
        });
        Route::prefix('tmon')->group(function () {
            Route::post('/', [TMonAccountController::class, 'add']);
            Route::post('edit', [TMonAccountController::class, 'edit']);
            Route::post('delete', [TMonAccountController::class, 'delete']);
        });
    });
    Route::prefix('product')->group(function () {
        Route::post('view', [ViewController::class, 'main']);
        Route::post('create-table', [PartnerTableController::class, 'create']);
        Route::post('update-table', [PartnerTableController::class, 'updatePartnerTable']);
        Route::post('delete-table', [PartnerTableController::class, 'deletePartnerTable']);
        Route::post('collect', [ManageController::class, 'add']);
        Route::post('upload', [UploadController::class, 'create']);
        Route::post('delete-product', [ManageController::class, 'deleteProduct']);
        Route::post('edit-product', [ManageController::class, 'editProduct']);
        Route::post('delete-uploaded', [UploadedController::class, 'delete']);
        Route::post('edit-uploaded', [UploadedController::class, 'edit']);
    });
    Route::prefix('notifications')->group(function () {
        Route::post('read-all', [NotificationController::class, 'readAll']);
    });
    Route::post('open-market-orders', [OpenMarketOrderController::class, 'indexPartner']);
    Route::prefix('excelwings')->group(function () {
        Route::post('export', [PartnerExcelwingController::class, 'downloadExcel']);
    });
});
Route::post('submit-contact-us', [BusinessPageController::class, 'submitContactUs']);
Route::post('sellwing-api/auth', [AuthController::class, 'main']);
