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
        $b2bs = DB::table('product_register AS pr')
            ->join('vendors AS v', 'v.id', '=', 'pr.vendor_id')
            ->where('pr.is_active', 'Y')
            ->where('v.is_active', 'ACTIVE')
            ->get();

        // 중복된 productName의 총 종류 수 찾기
        $totalDuplicateGroups = DB::table('minewing_products')
            ->select('productName')
            ->where('isActive', 'Y')
            ->groupBy('productName')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count(); // 중복 그룹의 총 개수를 계산

        // 첫 번째 중복된 productName을 가진 상품 찾기
        $duplicates = DB::table('minewing_products')
            ->select('productName', DB::raw('COUNT(*) as count'))
            ->where('isActive', 'Y')
            ->groupBy('productName')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        // 중복된 productName을 가진 상품들의 상세 정보 가져오기 (첫 번째 중복 그룹에 대해서만)
        $products = [];
        if ($duplicates) {
            $products = DB::table('minewing_products')
                ->where('isActive', 'Y')
                ->where('productName', $duplicates->productName)
                ->get(['productName', 'productHref', 'productImage', 'productCode', 'productPrice']);
        }

        return view('admin/namewing', [
            'duplicatedProducts' => $products,
            'b2bs' => $b2bs,
            'totalDuplicateGroups' => $totalDuplicateGroups // 중복 그룹의 총 개수 전달
        ]);
    }
}
