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
        $failed = [];
        foreach ($products as $index => $product) {
            if (!isset($product['hasOption'])) {
                $failed[] = $index + 1;
                continue;
            }
            $hasOption = $product['hasOption'];
            $byte = 50;
            if ($hasOption == true && isset($product['productOptions'])) {
                $byte = 40;
            }
            $productName = $nameController->index($product['productName'], $byte);
            $productImage = $productImageController->index($product['productImage'])['return'];
            $headerImage = DB::table('product_search')
                ->where('vendor_id', $product->sellerID)
                ->select('header_image')
                ->first()
                ->header_image;
            $productDetail = $productImageController->processImages($product['productDetail'], $headerImage);
            $productPrice = (int)$product['productPrice'];
            $productHref = $product['productHref'];
            $sellerID = $product['sellerID'];
            if ($hasOption == true && isset($product['productOptions'])) {
                $productOptions = $product['productOptions'];
                $optionPriceType = $this->getOptionPriceType($sellerID);
                $type = '1';
                foreach ($productOptions as $productOption) {
                    $newProductName = $productName . ' TYPE ' . $type;
                    $newProductDetail = '<h1 style="color:red; font-weight:bold; font-size:2rem;">옵션명 : ' . $productOption['optionName'] . '</h1>' . $productDetail;
                    if ($optionPriceType == 'ADD') {
                        (int)$productPrice = (int)$product['productOptions'] + (int)$productOption['optionPrice'];
                    } else {
                        $productPrice = (int)$productOption['optionPrice'];
                    }
                    $newProducts[] = [
                        'productName' => $newProductName,
                        'productImage' => $productImage,
                        'productPrice' => $productPrice,
                        'productDetail' => $newProductDetail,
                        'productHref' => $productHref,
                        'hasOption' => true,
                        'sellerID' => $sellerID,
                        'productNameOri' => $product['productName']
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
                    'hasOption' => false,
                    'sellerID' => $sellerID,
                    'productNameOri' => $product['productName']
                ];
            }
        }
        $nameDuplicates = $this->groupDuplicateProductIndices($newProducts);
        if ($nameDuplicates['status'] == true) {
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
    public function getOptionPriceType($sellerID)
    {
        $optionPriceType = DB::table('product_search')
            ->where('vendor_id', $sellerID)
            ->select('optionPriceType')
            ->first()
            ->optionPriceType;
        return $optionPriceType;
    }
    public function groupDuplicateProductIndices($products)
    {
        set_time_limit(0);
        $names = [];

        foreach ($products as $index => $product) {
            $name = $product['productName'];

            // DB 검증 또는 배열 내 중복 검사
            if ($this->validateProductNameFromDB($name)['status'] || in_array($name, $names)) {
                return [
                    'status' => true,
                    'productName' => $name,
                    'index' => $index,
                    'productNameOri' => $product['productNameOri']
                ];
            }

            $names[] = $name;
        }

        // 중복된 제품 이름이 없음
        return ['status' => false];
    }
    protected function validateProductNameFromDB($productName)
    {
        $isDuplicated = DB::table('minewing_products')
            ->where('productName', $productName)
            ->where('isActive', 'Y')
            ->get();
        if (count($isDuplicated) > 0) {
            return [
                'status' => true,
                'return' => $productName
            ];
        } else {
            return [
                'status' => false
            ];
        }
    }
}
