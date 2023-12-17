<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Admin\ProductDataValidityController;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsertController extends Controller
{
    public function index(Request $request)
    {
        set_time_limit(0);
        $remember_token = $request->remember_token;
        $products = $request->products;
        $categoryID = $request->categoryID;
        $productKeywords = $request->productKeywords;
        $isValid = $this->validateElements($categoryID, $productKeywords);
        if (!$isValid['status']) {
            return $isValid;
        }
        $userID = DB::table('users')
            ->where('remember_token', $remember_token)
            ->where('is_active', 'ACTIVE')
            ->select('id')
            ->first()
            ->id;
        if ($userID === null) {
            return [
                'status' => false,
                'return' => '잘못된 접근입니다.'
            ];
        }
        return $this->insertProducts($products, $userID, $categoryID, $productKeywords);
    }
    public function validateElements($categoryID, $productKeywords)
    {
        $productDataValidityController = new ProductDataValidityController();
        $isValid = $productDataValidityController->index($categoryID, $productKeywords);
        return $isValid;
    }
    public function validateCategoryID($categoryID)
    {
        $isExist = DB::table('ownerclan_category')
            ->where('id', $categoryID)
            ->exists();
        return $isExist;
    }
    public function insertProducts($products, $userID, $categoryID, $productKeywords)
    {
        try {
            foreach ($products as $product) {
                $hasOption = 'N';
                if ($product['hasOption'] === 'true') {
                    $hasOption = 'Y';
                }
                $productCode = $this->generateRandomProductCode(5);
                $productName = $product['productName'];
                $productPrice = $product['productPrice'];
                $productImage = $product['productImage'];
                $productDetail = $product['productDetail'];
                $productdHref = $product['productHref'];
                $sellerID = $product['sellerID'];
                DB::table('minewing_products')
                    ->insert([
                        'sellerID' => $sellerID,
                        'userID' => $userID,
                        'categoryID' => $categoryID,
                        'productCode' => $productCode,
                        'productName' => $productName,
                        'productKeywords' => $productKeywords,
                        'productPrice' => $productPrice,
                        'productImage' => $productImage,
                        'productDetail' => $productDetail,
                        'productHref' => $productdHref,
                        'hasOption' => $hasOption
                    ]);
                $response = $this->insertMappingwing($categoryID);
                if (!$response) {
                    return [
                        'status' => true,
                        'return' => '"매핑윙 연동에 실패했습니다."'
                    ];
                }
            }
            return [
                'status' => true,
                'return' => '"상품셋을 성공적으로 저장했습니다."'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => '"상품셋 저장에 실패했습니다. 기술자에게 문의해주세요.<br>"' . $e->getMessage()
            ];
        }
    }
    protected function insertMappingwing($ownerclanCategoryID)
    {
        try {
            $isExist = DB::table('category_mapping')
                ->where('ownerclan', $ownerclanCategoryID)
                ->exists();
            if (!$isExist) {
                DB::table('category_mapping')
                    ->insert([
                        'ownerclan' => $ownerclanCategoryID
                    ]);
            }
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    protected function generateRandomProductCode($length)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charactersLength = strlen($characters);
        $randomCode = '';

        for ($i = 0; $i < $length; $i++) {
            $randomCode .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomCode;
    }
}
