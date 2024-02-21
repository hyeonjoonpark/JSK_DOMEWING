<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ThreemroTopBannerController extends Controller
{
    public function index()
    {
        $targetProducts = DB::table('minewing_products')
            ->where('sellerID', 16)
            ->where('productDetail', 'LIKE', '%-top%')
            ->select('id', 'productDetail')
            ->get();
        foreach ($targetProducts as $product) {
            // -top을 포함하는 이미지 파일명을 가진 <img> 태그를 찾아 제거
            $updatedProductDetail = preg_replace('/<img src="[^"]*-top[^"]*">/', '', $product->productDetail);
            DB::table('minewing_products')
                ->where('id', $product->id)
                ->update([
                    'productDetail' => $updatedProductDetail
                ]);
        }
        return 'success';
    }
}
