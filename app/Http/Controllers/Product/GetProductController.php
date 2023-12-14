<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetProductController extends Controller
{
    public function index(Request $request)
    {
        $productID = $request->productID;
        $product = $this->getProduct($productID);
        if ($product === null) {
            return [
                'status' => false,
                'return' => '상품 정보 불러오기에 실패했습니다. 다시 시도해주십시오.'
            ];
        }
        return [
            'status' => true,
            'return' => $product
        ];
    }
    public function getProduct($productID)
    {
        $product = DB::table('minewing_products')
            ->where('isActive', 'Y')
            ->where('id', $productID)
            ->first();
        return $product;
    }
}
