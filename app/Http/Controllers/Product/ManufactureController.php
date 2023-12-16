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
            $productDetail = $productImageController->processImages($product['productDetail']);
            $productPrice = (int)$product['productPrice'];
            $productHref = $product['productHref'];
            $sellerID = $product['sellerID'];
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
                        'hasOption' => true,
                        'sellerID' => $sellerID
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
                    'sellerID' => $sellerID
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
    public function groupDuplicateProductIndices($products)
    {
        set_time_limit(0);
        $names = [];
        foreach ($products as $index => $product) {
            $name = $product['productName'];
            $isDuplicated = $this->validateProductNameFromDB($name);
            if ($isDuplicated['status']) {
                $dupNameIndex[$name][] = $index;
                return [
                    'status' => true,
                    'productName' => $name,
                    'index' => $index
                ];
            }
            if (in_array($name, $names)) {
                $dupNameIndex[$name][] = $index;
                return [
                    'status' => true,
                    'productName' => $name,
                    'index' => $index
                ];
            }
            $names[] = $name;
        }
        return [
            'status' => false
        ];
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
