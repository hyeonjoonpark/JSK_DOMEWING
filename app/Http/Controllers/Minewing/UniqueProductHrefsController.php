<?php

namespace App\Http\Controllers\Minewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UniqueProductHrefsController extends Controller
{
    public function index(Request $request)
    {
        set_time_limit(0);
        // Get unique product hrefs directly.
        $productHrefs = $request->productHrefs;
        if (!is_array($productHrefs)) {
            return [
                'status' => false,
                'return' => [
                    'message' => '수집 및 가공을 진행할 상품들을 선택해주세요.'
                ]
            ];
        }
        $uniqueProductHrefs = array_unique($request->productHrefs);

        // Get existing active product hrefs from the database in one query.
        $existingProductHrefs = DB::table('minewing_products')
            ->whereIn('productHref', $uniqueProductHrefs)
            ->where('isActive', 'Y')
            ->pluck('productHref')
            ->toArray();

        // Use array_diff to find new product hrefs.
        $newProductHrefs = array_diff($uniqueProductHrefs, $existingProductHrefs);
        if (count($newProductHrefs) < 1) {
            return [
                'status' => false,
                'return' => [
                    'message' => '이미 수집된 상품셋입니다.'
                ]
            ];
        }
        return [
            'status' => true,
            'return' => [
                'productHrefs' => $newProductHrefs,
                'numDuplicated' => count($existingProductHrefs)
            ]
        ]; // Reset keys and return
    }
}
