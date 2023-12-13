<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;

use function PHPUnit\Framework\isEmpty;

class ManufactureController extends Controller
{
    public function index(Request $request)
    {
        set_time_limit(0);
        $products = $request->productDetails;
        $nameController = new NameController();
        $productImageController = new ProductImageController();
        $newProducts = [];
        foreach ($products as $product) {
            $hasOption = $product['hasOption'];
            $byte = 50;
            if ($hasOption == true && isset($product['productOptions'])) {
                $byte = 40;
            }
            $productName = $nameController->index($product['productName'], $byte);
            $productImage = $productImageController->index($product['productImage'])['return'];
            $productDetail = $productImageController->extractImageSrcFromHtml($product['productDetail']);
            $productPrice = (int)$product['productPrice'];
            $productHref = $product['productHref'];
            if ($hasOption == true && isset($product['productOptions'])) {
                $productOptions = $product['productOptions'];
                $type = 'A';
                foreach ($productOptions as $productOption) {
                    $newProductName = $productName . ' TYPE ' . $type;
                    $newProductDetail = '<h1>옵션명 : ' . $productOption['optionName'] . '</h1>' . $productDetail;
                    $productPrice = (int)$productOption['optionPrice'];
                    $newProducts[] = [
                        'productName' => $newProductName,
                        'productImage' => $productImage,
                        'productPrice' => $productPrice,
                        'productDetail' => $newProductDetail,
                        'productHref' => $productHref,
                        'hasOption' => true
                    ];
                    $type++;
                }
            } else {
                $newProducts[] = [
                    'productName' => $productName,
                    'productImage' => $productImage,
                    'productPrice' => $productPrice,
                    'productDetail' => $productDetail,
                    'productHref' => $productHref,
                    'hasOption' => false
                ];
            }
        }
        $nameDuplicates = $this->groupDuplicateProductIndices($newProducts);
        if (count($nameDuplicates) > 0) {
            return [
                'status' => false,
                'return' => $newProducts,
                'duplicates' => $nameDuplicates
            ];
        }
        return [
            'status' => true,
            'return' => $newProducts
        ];
    }
    protected function groupDuplicateProductIndices($products)
    {
        $indicesMap = []; // 각 productName에 대한 인덱스들을 저장할 배열

        foreach ($products as $index => $product) {
            if (!isset($product['productName'])) {
                continue; // productName 키가 없는 경우는 건너뛰기
            }

            $name = $product['productName'];

            // productName에 해당하는 인덱스들을 배열에 추가
            $indicesMap[$name][] = $index;
        }

        // 중복된 productName만 필터링
        $duplicateGroups = array_filter($indicesMap, function ($indices) {
            return count($indices) > 1;
        });

        return $duplicateGroups; // 중복된 productName의 인덱스 그룹 반환
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
