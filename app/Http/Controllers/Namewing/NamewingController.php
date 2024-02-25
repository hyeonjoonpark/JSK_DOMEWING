<?php

namespace App\Http\Controllers\Namewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NamewingController extends Controller
{
    /**
     * 중복된 productName을 가진 상품들을 찾아 반환합니다.
     *
     * @return \Illuminate\Http\Response
     */
    public function main()
    {
        // 중복된 productName을 가진 상품 찾기
        $duplicates = DB::table('minewing_products')
            ->select('productName', DB::raw('COUNT(*) as count'))
            ->where('isActive', 'Y')
            ->groupBy('productName')
            ->havingRaw('COUNT(*) > 1')
            ->first();
        // 중복된 productName을 가진 상품들의 상세 정보 가져오기
        $products = DB::table('minewing_products')
            ->where('isActive', 'Y')
            ->where('productName', $duplicates->productName)
            ->get(['productName', 'productHref', 'productImage', 'productCode']);
        return view('admin/namewing', [
            'duplicatedProducts' => $products
        ]);
    }
}
