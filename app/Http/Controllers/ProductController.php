<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Minewing\SaveController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function create(Request $request)
    {
    }
    public function store(int $sellerId, int $categoryId, string $productName, string $productKeywords, int $productPrice, int $shippingFee, int $bundleQuantity, string $productImage, string $productDetail, string $productHref, string $hasOption, string $remark = '')
    {
        $sc = new SaveController();
        try {
            $product = DB::table('minewing_products')
                ->insert([
                    'sellerID' => $sellerId,
                    'userID' => 15,
                    'categoryID' => $categoryId,
                    'productCode' => $sc->generateRandomProductCode(8),
                    'productName' => $productName,
                    'productKeywords' => $productKeywords,
                    'productPrice' => $productPrice,
                    'shipping_fee' => $shippingFee,
                    'bundle_quantity' => $bundleQuantity,
                    'productImage' => $productImage,
                    'productDetail' => $productDetail,
                    'productHref' => $productHref,
                    'hasOption' => $hasOption,
                    'remark' => $remark
                ]);
            return [
                'status' => true,
                'message' => "신규 상품을 성공적으로 생성하였습니다.",
                'data' => $product
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => "신규 상품을 생성하는 과정에서 오류가 발생했습니다.",
                'error' => $e->getMessage()
            ];
        }
    }
}
