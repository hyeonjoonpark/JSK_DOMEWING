<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetProductController extends Controller
{
    public function index(Request $request)
    {
        $productCode = $request->productCode;
        $product = $this->getProduct($productCode);
        if ($product == null) {
            return [
                'status' => false,
                'return' => '상품 정보를 찾을 수 없습니다.'
            ];
        }
        return [
            'status' => true,
            'return' => $product
        ];
    }
    public function getProduct($productCode)
    {
        return DB::table('minewing_products')
            ->where('productCode', $productCode)
            ->first();
    }
}
