<?php

namespace App\Http\Controllers\APIwing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Minewing\SaveController;
use Illuminate\Support\Facades\DB;

class InsertController extends Controller
{
    public function index($sellerID)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $controller = new Controller();
        $seller = $controller->getSeller($sellerID);
        $table = $seller->name_eng . '_products';
        $products = DB::table($table)
            ->whereNotNull('categoryID')
            ->get();
        $success = [];
        $error = [];
        foreach ($products as $product) {
            $response = $this->moveProduct($product);
            if ($response['status'] == true) {
                $success[] = $product->id;
                $error[] = $product->id;
            }
        }
        return [
            'success' => count($success),
            'error' => count($error),
        ];
    }
    public function moveProduct($product)
    {
        try {
            $productName = $product->productName;
            $sellerID = $product->sellerID;
            $userID = $product->userID;
            $categoryID = $product->categoryID;
            $controller = new Controller();
            $productCode = $controller->getProductCode('minewing_products'); // 가정: getProductCode 메소드는 적절한 결과를 반환합니다.
            $productKeywords = $product->productKeywords;
            $productPrice = $product->productPrice;
            $productImage = $product->productImage;
            $productDetail = $product->productDetail;
            $productHref = $product->productHref;
            $hasOption = $product->hasOption;
            $product = DB::table('minewing_products')
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
                    'productHref' => $productHref,
                    'hasOption' => $hasOption
                ]);
            $saveController = new SaveController();
            $response = $saveController->insertMappingwing($categoryID);
            if (!$response) {
                return [
                    'status' => false,
                    'return' => '"매핑윙 연동에 실패했습니다."'
                ];
            }
            return [
                'status' => true,
                'return' => $product,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage(),
            ];
        }
    }
}
