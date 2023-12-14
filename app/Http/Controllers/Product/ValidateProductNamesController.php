<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ValidateProductNamesController extends Controller
{
    public function index(Request $request)
    {
        $newProductName = $request->newProductName;
        $index = $request->index;
        $products = $request->products;
        $nameController = new NameController();
        $filteredNewProductName = $nameController->index($newProductName, 50);
        $products[$index]['productName'] = $filteredNewProductName;
        $manufactureController = new ManufactureController();
        $isDup = $manufactureController->groupDuplicateProductIndices($products);
        if ($isDup['status']) {
            return [
                'status' => false,
                'return' => $products,
                'duplicates' => $isDup
            ];
        }
        return [
            'status' => true,
            'return' => $products
        ];
    }
}
