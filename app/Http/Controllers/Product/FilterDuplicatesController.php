<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterDuplicatesController extends Controller
{
    public function index(Request $request)
    {
        set_time_limit(0);
        $productHrefs = $request->productHrefs;
        $duplicates = $this->getDuplicateHrefs($productHrefs);

        // 중복 항목 제거
        $uniqueProductHrefs = array_diff($productHrefs, $duplicates);

        return [
            'status' => true,
            'return' => [
                'message' => '"총 ' . count($duplicates) . '개의 중복 상품들을 제외했어요.<br>상품 정보들을 수집해올게요."',
                'uniqueProductHrefs' => $uniqueProductHrefs
            ]
        ];
    }

    public function getDuplicateHrefs($productHrefs)
    {
        $duplicates = DB::table('minewing_products')
            ->where('isActive', 'Y')
            ->whereIn('productHref', $productHrefs)
            ->pluck('productHref'); // 중복된 productHref만 가져옴

        return $duplicates->toArray();
    }
}
