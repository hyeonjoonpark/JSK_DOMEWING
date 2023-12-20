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
        $activeDuplicates = $this->getActiveDuplicateHrefs($uniqueProductHrefs);

        // 중복 항목 제거
        $finalProductHrefs = array_diff($uniqueProductHrefs, $activeDuplicates);
        $numDuplicates = count($productHrefs) - count($finalProductHrefs);

        // 응답 메시지 생성
        $responseMessage = "총 {$numDuplicates}개의 중복 상품들을 제외했어요.<br>상품 정보들을 수집해올게요.";

        return [
            'status' => true,
            'return' => [
                'message' => $responseMessage,
                'uniqueProductHrefs' => $finalProductHrefs
            ]
        ];
    }

    public function getActiveDuplicateHrefs($productHrefs)
    {
        // DB에서 활성화된 중복 상품 링크 가져오기
        return DB::table('minewing_products')
            ->where('isActive', 'Y')
            ->whereIn('productHref', $productHrefs)
            ->pluck('productHref')
            ->toArray();
    }
}
