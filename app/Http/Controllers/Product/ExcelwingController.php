<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Admin\FormProductController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExcelwingController extends Controller
{
    public function index(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        $b2BID = $request->b2BID;
        $sellerIDs = $request->sellerIDs;
        $response = $this->getProducts($sellerIDs);
        if (!$response['status']) {
            return $response;
        }
        $products = $response['return'];
        $response = $this->getMarginRate($b2BID);
        if (!$response['status']) {
            return $response;
        }
        $marginRate = $response['return'];
        $response = $this->getVendor($b2BID);
        if (!$response['status']) {
            return $response;
        }
        $b2B = $response['return'];
        $vendorEngName = $b2B->name_eng;
        $formProductController = new FormProductController();
        $response = $formProductController->$vendorEngName($products, $marginRate, $vendorEngName);
        return $response;
    }
    protected function getProducts($sellerIDs)
    {
        $products = DB::table("minewing_products")
            ->where("isActive", "Y")
            ->whereIn("sellerID", $sellerIDs)
            ->get();
        return [
            "status" => true,
            "return" => $products
        ];
    }
    protected function getMarginRate($vendorID)
    {
        $marginRate = DB::table("margin_rate")
            ->where("vendorID", $vendorID)
            ->first();
        if ($marginRate) {
            $marginRate = (int)$marginRate->rate;
            $marginRate = (float)((100 + $marginRate) / 100);
            return [
                'status' => true,
                'return' => $marginRate
            ];
        } else {
            // userID가 검출되지 않았을 때의 처리 (예: false 반환)
            return [
                'status' => false,
                'return' => "잘못된 접근입니다. 다시 로그인하여 주십시오."
            ];
        }
    }
    protected function getUser($remember_token)
    {
        $user = DB::table("users")
            ->where("remember_token", $remember_token)
            ->first();
        if ($user) {
            // userID가 검출되었을 때의 처리
            return [
                'status' => true,
                'return' => $user
            ];
        } else {
            // userID가 검출되지 않았을 때의 처리 (예: false 반환)
            return [
                'status' => false,
                'return' => "잘못된 접근입니다. 다시 로그인하여 주십시오."
            ];
        }
    }
    protected function getVendor($vendorID)
    {
        $vendor = DB::table("product_register AS pr")
            ->join("vendors AS v", 'pr.vendor_id', '=', 'v.id')
            ->where("v.is_active", "ACTIVE")
            ->where("pr.is_active", "Y")
            ->where('v.id', $vendorID)
            ->select("v.id", "v.name", "v.name_eng")
            ->first();
        if ($vendor) {
            return [
                'status' => true,
                'return' => $vendor
            ];
        } else {
            // userID가 검출되지 않았을 때의 처리 (예: false 반환)
            return [
                'status' => false,
                'return' => "잘못된 접근입니다. 다시 로그인하여 주십시오."
            ];
        }
    }
}
