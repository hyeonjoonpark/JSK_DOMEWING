<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductRegisterController;
use App\Http\Controllers\Admin\ProductSearchController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ImageUploadController;

use App\Http\Controllers\Admin\DomainController;
use App\Http\Controllers\Admin\CMSController;

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

Route::post('/admin/remove-domain', [DomainController::class, 'removeDomain']);
Route::post('/admin/register-domain', [DomainController::class, 'registerDomain']);
Route::get('/admin/get-domain', [DomainController::class, 'getDomain']);
Route::post('admin/edit-domain', [DomainController::class, 'editDomain']);
Route::post('admin/upload-image-banner', [CMSController::class, 'uploadImageBanner']);
Route::post('admin/change-image-status', [CMSController::class, 'changeImageStatus']);
Route::post('admin/remove-image-banner', [CMSController::class, 'removeImage']);
Route::post('admin/change-theme-color', [CMSController::class, 'changeThemeColor']);
