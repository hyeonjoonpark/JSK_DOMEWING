<?php

namespace App\Http\Controllers\Minewing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\NameController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManufactureController extends Controller
{
    public function index(Request $request)
    {
        ini_set('max_input_vars', '100000');
        set_time_limit(0);
        $products = $request->products;
        $nameController = new NameController();
        $productNames = [];
        foreach ($products as $i => &$product) {
            $productName = $product['productName'];
            $hasOption = $product['hasOption'];
            $byte = 50;
            if ($hasOption === true) {
                $byte = 42;
            }
            $productName = $nameController->index($productName, $byte);
            $duplicateIndex = array_search($productName, $productNames);
            if ($duplicateIndex !== false) {
                return [
                    'status' => false,
                    'return' => [
                        'type' => 'FROM_ARRAY',
                        'duplicatedProductName' => $productName,
                        'duplicatedIndex' => $duplicateIndex,
                        'index' => $i,
                        'products' => $products
                    ]
                ];
            }
            $isDuplicated = $this->isDuplicated($productName);
            if ($isDuplicated != null) {
                return [
                    'status' => false,
                    'return' => [
                        'type' => 'FROM_DB',
                        'duplicatedProductName' => $productName,
                        'index' => $i,
                        'products' => $products,
                        'duplicatedProduct' => $isDuplicated
                    ]
                ];
            }
            $product['productName'] = $productName;
            $productNames[] = $productName;
        }
        unset($product);
        return [
            'status' => true,
            'return' => $products
        ];
    }
    public function isDuplicated($productName)
    {
        $duplicatedProduct = DB::table('minewing_products')
            ->where('productName', $productName)
            ->where('isActive', 'Y')
            ->first();
        return $duplicatedProduct;
    }
}
