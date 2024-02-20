<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    public function getVendor($vendorID)
    {
        try {
            $vendor = DB::table('vendors')
                ->where('is_active', 'ACTIVE')
                ->where('id', $vendorID)
                ->first();
            if ($vendor == '') {
                return [
                    'status' => false,
                    'return' => '유효한 업체가 아닙니다.'
                ];
            }
            return [
                'status' => true,
                'return' => $vendor
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage()
            ];
        }
    }
    public function getSeller($sellerID)
    {
        $seller = DB::table('product_search AS ps')
            ->join('vendors AS v', 'v.id', '=', 'ps.vendor_id')
            ->where('ps.is_active', 'Y')
            ->where('v.is_active', 'ACTIVE')
            ->where('ps.vendor_id', $sellerID)
            ->first();
        return $seller;
    }
    public function getProductCode($table, $length = 5)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charactersLength = strlen($characters);
        do {
            $productCode = '';
            for ($i = 0; $i < $length; $i++) {
                $productCode .= $characters[rand(0, $charactersLength - 1)];
            }
            $isExist = DB::table($table)
                ->where('productCode', $productCode)
                ->where('isActive', 'Y')
                ->exists();
        } while ($isExist);
        return $productCode;
    }
    public function insertProduct($table, $sellerID, $userID, $categoryName, $productName, $productKeywords, $productPrice, $productImage, $productDetail, $productHref, $hasOption)
    {
        try {
            $product = DB::table($table)
                ->insert([
                    'sellerID' => $sellerID,
                    'userID' => $userID,
                    'categoryName' => $categoryName,
                    'productName' => $productName,
                    'productKeywords' => $productKeywords,
                    'productPrice' => $productPrice,
                    'productImage' => $productImage,
                    'productDetail' => $productDetail,
                    'productHref' => $productHref,
                    'hasOption' => $hasOption
                ]);
            return [
                'status' => true,
                'return' => $product
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage()
            ];
        }
    }
    // 업체 계정 가져오기
    protected function getVendorAccount($rememberToken, $vendorId)
    {
        $account = DB::table('accounts')
            ->join('users', 'users.id', '=', 'accounts.user_id')
            ->join('vendors', 'vendors.id', '=', 'accounts.vendor_id')
            ->where('users.remember_token', $rememberToken)
            ->where('vendors.id', $vendorId)
            ->where('accounts.is_active', 'Y')
            ->select('accounts.username', 'accounts.password')
            ->first();
        return $account;
    }
    protected function getActiveB2Bs()
    {
        return DB::table('product_register AS pr')
            ->join('vendors AS v', 'v.id', '=', 'pr.vendor_id')
            ->where('v.is_active', 'ACTIVE')
            ->where('pr.is_active', 'Y')
            ->get();
    }
}
