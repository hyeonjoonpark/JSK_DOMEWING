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
        $sellerID = $request->sellerID;
        $shippingFee = $this->getShippingFee($sellerID);
        $response = $this->getProducts($sellerID);
        if (!$response['status']) {
            return $response;
        }
        $products = $response['return'];
        $products = $products->toArray();
        // $b2BID에 따른 $numChunks 값 매핑
        $numChunksMapping = [
            35 => 300,
            33 => 100,
        ];

        // 기본값 설정
        $numChunks = 500;

        // $b2BID 값에 해당하는 매핑이 있는 경우, 그 값을 사용
        if (array_key_exists((int)$b2BID, $numChunksMapping)) {
            $numChunks = $numChunksMapping[(int)$b2BID];
        }

        // $products 배열을 $numChunks 크기의 청크로 나눔
        $productsChunks = array_chunk($products, $numChunks);

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
        $downloadURLs = [];
        foreach ($productsChunks as $index => $products) {
            $response = $formProductController->$vendorEngName($products, $marginRate, $vendorEngName, $shippingFee, $index);
            if ($response['status'] == true) {
                $downloadURLs[] = $response['return'];
            } else {
                return [
                    'status' => false,
                    'return' => '"엑셀 파일에 데이터를 기록하던 중 오류가 발생했습니다."'
                ];
            }
        }
        return [
            'status' => true,
            'return' => $downloadURLs
        ];
    }
    public function getShippingFee($vendorID)
    {
        $shippingFee = DB::table('product_search')
            ->where('vendor_id', $vendorID)
            ->select('shipping_fee')
            ->first()
            ->shipping_fee;
        return $shippingFee;
    }
    public function getProducts($sellerID)
    {
        $products = DB::table("minewing_products")
            ->where("isActive", "Y")
            ->where("sellerID", $sellerID)
            ->get();
        return [
            "status" => true,
            "return" => $products
        ];
    }
    public function getMarginRate($vendorID)
    {
        $marginRate = DB::table("product_register AS pr")
            ->where("pr.vendor_id", $vendorID)
            ->first();
        if ($marginRate) {
            $marginRate = (int)$marginRate->margin_rate;
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
    public function getUser($remember_token)
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
    public function getVendor($vendorID)
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
