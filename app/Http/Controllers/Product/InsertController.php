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
            ->first();
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
        foreach ($products as $product) {
            $sellerID = $product['sellerID'];
            $hasOption = false;
            if ($product['hasOption'] === true) {
                $hasOption = true;
            }
            try {
                DB::table('minewing_products')
                    ->insert([
                        'sellerID' => $sellerID,
                        'userID' => $userID,
                        'categoryID' => $categoryID,
                        'productCode' => $this->generateRandomProductCode(),
                        'productName' => $product['productName'],
                        'productKeywords' => $productKeywords,
                        'productPrice' => $product['productPrice'],
                        'productImage' => $product['productImage'],
                        'productDetail' => $product['productDetail'],
                        'productHref' => $product['productHref'],
                        'hasOption' => $hasOption
                    ]);
                return [
                    'status' => true,
                    'return' => '"상품셋을 성공적으로 저장했습니다."'
                ];
            } catch (\Exception $e) {
                return [
                    'status' => false,
                    'return' => '"상품셋 저장에 실패했습니다. 기술자에게 문의해주세요."'
                ];
            }
        }
    }
    protected function generateRandomProductCode($length = 5)
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
