<?php

namespace App\Http\Controllers\Minewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UniqueProductHrefsController extends Controller
{
    const MAX_INPUT_VARS = '100000';
    const TIME_LIMIT = 0;

    public function index(Request $request)
    {
        $this->setEnvironment();
        $productHrefs = $this->getValidatedProductHrefs($request->productHrefs);
        if ($productHrefs === false) {
            return [
                'status' => false,
                'return' => '수집 및 가공을 진행할 상품들을 선택해주세요.'
            ];
        }
        $uniqueProductHrefs = array_values(array_unique($productHrefs));
        $existingProductHrefs = $this->getExistingProductHrefs($uniqueProductHrefs);
        $newProductHrefs = array_values(array_diff($uniqueProductHrefs, $existingProductHrefs));
        $numDuplicated = count($existingProductHrefs) + count($productHrefs) - count($uniqueProductHrefs);
        if (empty($newProductHrefs)) {
            return [
                'status' => false,
                'return' => '이미 수집된 상품셋입니다.'
            ];
        }
        return [
            'status' => true,
            'return' => [
                'numDuplicated' => $numDuplicated,
                'productHrefs' => $newProductHrefs
            ]
        ];
    }

    private function setEnvironment()
    {
        set_time_limit(self::TIME_LIMIT);
        ini_set('max_input_vars', self::MAX_INPUT_VARS);
    }

    private function getValidatedProductHrefs($productHrefs)
    {
        return is_array($productHrefs) ? $productHrefs : false;
    }

    private function getExistingProductHrefs($uniqueProductHrefs)
    {
        return DB::table('minewing_products')
            ->whereIn('productHref', $uniqueProductHrefs)
            ->where('isActive', 'Y')
            ->groupBy('productHref')
            ->pluck('productHref')
            ->toArray();
    }
}
