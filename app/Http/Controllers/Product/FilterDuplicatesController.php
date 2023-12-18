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
        $uniqueProductHrefs = array_unique($productHrefs);
        $numDuplicates = count($productHrefs) - count($uniqueProductHrefs);
        $duplicates = $this->getDuplicateHrefs($uniqueProductHrefs);

        // 중복 항목 제거
        $finalProductHrefs = array_diff($uniqueProductHrefs, $duplicates);
        $numDuplicates = $numDuplicates + (count($uniqueProductHrefs) - count($duplicates));

        return [
            'status' => true,
            'return' => [
                'message' => '"총 ' . $numDuplicates . '개의 중복 상품들을 제외했어요.<br>상품 정보들을 수집해올게요."',
                'uniqueProductHrefs' => $finalProductHrefs
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
