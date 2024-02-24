<?php

namespace App\Http\Controllers\Minewing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\NameController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManufactureController extends Controller
{
    const OPTION_BYTE = 42;
    const DEFAULT_BYTE = 50;

    public function index(Request $request)
    {
        ini_set('max_input_vars', '100000');
        set_time_limit(0);

        $products = $request->products;
        $productNames = [];
        foreach ($products as $i => &$product) {
            $product['productName'] = $this->processProductName($product, $i, $productNames);
            // if ($product['productName'] === false) {
            //     return $this->createErrorResponse($productNames, $products, $i);
            // }
            $productNames[] = $product['productName'];
        }
        unset($product); // Break reference link.

        return ['status' => true, 'return' => $products];
    }

    private function processProductName(&$product, $index, $productNames)
    {
        $nameController = new NameController();
        $byte = $product['hasOption'] === true ? self::OPTION_BYTE : self::DEFAULT_BYTE;
        $productName = $nameController->index($product['productName'], $byte);

        if (in_array($productName, $productNames) || $this->isDuplicated($productName)) {
            return false;
        }

        return $productName;
    }

    private function createErrorResponse($productNames, $products, $index)
    {
        $productName = $products[$index]['productName'];
        $duplicateIndex = array_search($productName, $productNames);
        $isDuplicated = $this->isDuplicated($productName);

        $errorType = $isDuplicated ? 'FROM_DB' : 'FROM_ARRAY';
        $duplicatedProduct = $isDuplicated ?: null;

        return [
            'status' => false,
            'return' => [
                'type' => $errorType,
                'duplicatedProductName' => $productName,
                'duplicatedIndex' => $duplicateIndex,
                'index' => $index,
                'products' => $products,
                'duplicatedProduct' => $duplicatedProduct,
            ],
        ];
    }

    public function isDuplicated($productName)
    {
        return DB::table('minewing_products')
            ->where('productName', $productName)
            ->where('isActive', 'Y')
            ->first();
    }
}
