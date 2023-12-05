<?php

use App\Http\Controllers\Admin\AccountSettingController;
use App\Http\Controllers\Admin\CMSController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DomainController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\ImageUploadController;
use App\Http\Controllers\Admin\ProductCollectController;
use App\Http\Controllers\Admin\ProductDataValidityController;
use App\Http\Controllers\Admin\ProductDetailController;
use App\Http\Controllers\Admin\ProductKeywordController;
use App\Http\Controllers\Admin\ProductRegisterController;
use App\Http\Controllers\Admin\ProductSearchController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Domewing\ProductDetailsController;
use App\Http\Controllers\Domewing\ShoppingCartController;
use App\Http\Controllers\Domewing\OrderController;
use App\Http\Controllers\Domewing\CheckoutController;
use App\Http\Controllers\Domewing\MemberController;
use App\Http\Controllers\Domewing\ToShipController;
use App\Http\Controllers\Domewing\ToReceiveController;

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
Route::post('/webhook', [WebhookController::class, 'webhook']);
Route::post('/set-post-confirmed', [DashboardController::class, 'setPostConfirmed']);
Route::post('/delete-post', [DashboardController::class, 'deletePost']);
Route::post('/product/search', [ProductSearchController::class, 'productSearch']);
Route::post('/product/category', [ProductRegisterController::class, 'categorySearch']);
Route::post('/product/register', [ProductRegisterController::class, 'handle']);
Route::post('/product/upload', [FormController::class, 'index']);
Route::post('/product/keywords', [ProductKeywordController::class, 'index']);
Route::post('/product/data-validity', [ProductDataValidityController::class, 'index']);
// account-setting
Route::post('/account-setting/margin-rate', [AccountSettingController::class, 'changeMarginRate']);
// Load product details
Route::post('/product/load-product-detail', [ProductDetailController::class, 'index']);
Route::post('/product/collect', [ProductCollectController::class, 'index']);
Route::post('/product/load-bulk-details', [ProductDetailController::class, 'bulk']);
Route::post('/product/insert-bulk-products', [ProductCollectController::class, 'bulk']);

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
Route::post('member/remove-cart-item', [ShoppingCartController::class, 'removeCartItem']);
Route::post('member/update-quantity', [ShoppingCartController::class, 'updateQuantity']);
Route::post('member/create-order', [OrderController::class, 'createOrder']);
Route::post('member/checkout-order', [CheckoutController::class, 'checkoutOrder']);

Route::post('member/update-profile', [MemberController::class, 'updateProfile']);
Route::get('member/get-transaction-details/{id}', [MemberController::class, 'getTransactionDetails']);
