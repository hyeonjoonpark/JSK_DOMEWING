<?php

namespace App\Http\Controllers\Partners\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ViewController extends Controller
{
    public function main(Request $request)
    {
        $productCode = $request->input('productCode');
        $controller = new Controller();
        $product = $controller->getProductWithCode($productCode);
        if ($product === null) {
            return [
                'status' => false,
                'message' => '유효한 상품 코드가 아닙니다. 페이지를 새로고침해주십시오.'
            ];
        }
        return [
            'status' => true,
            'data' => $product
        ];
    }
}
