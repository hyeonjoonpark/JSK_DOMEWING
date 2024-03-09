<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Product\NameController;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    private $nameController;
    public function __construct()
    {
        $this->nameController = new NameController();
    }
    public function main()
    {
        $products = $this->getTargetProducts();
        $newProductName = [];
        foreach ($products as $product) {
            $updateProductNameResult = $this->updateProductName($product);
            if ($updateProductNameResult['status'] === false) {
                return $updateProductNameResult['return'];
            }
            $productId[] = $updateProductNameResult['return'];
        }
        return $productId;
    }
    private function getTargetProducts()
    {
        return DB::table('minewing_products')
            ->where('createdAt', '>', '2024-02-29')
            ->get(['productName', 'id']);
    }
    private function updateProductName($product)
    {
        $productId = $product->id;
        $productName = $product->productName;
        $pattern = '/옵션 (\d+)/';
        if (preg_match($pattern, $productName, $matches)) {
            $safeProductName = trim(str_replace($matches[0], '', $productName));
            $newProductName = $this->nameController->index($safeProductName);
            $newProductName .= ' 옵션 ' . $matches[1];
        } else {
            $newProductName = $this->nameController->index($productName);
        }
        try {
            DB::table('minewing_products')
                ->where('id', $productId)
                ->update([
                    'productName' => $newProductName,
                    'updatedAt' => now()
                ]);
            return [
                'status' => true,
                'return' => $productId
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage()
            ];
        }
    }
}
