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
        $requestedProductHrefs = array_map('trim', $request->productHrefs);
        $productHrefs = $this->getValidatedProductHrefs($requestedProductHrefs);
        if ($productHrefs === false) {
            return $this->errorResponse('수집 및 가공을 진행할 상품들을 선택해주세요.');
        }

        $uniqueProductHrefs = array_values(array_unique($productHrefs));
        $existingProductHrefs = $this->getExistingProductHrefs($uniqueProductHrefs);
        $newProductHrefs = array_values(array_diff($uniqueProductHrefs, $existingProductHrefs));

        if (empty($newProductHrefs)) {
            return $this->errorResponse('이미 수집된 상품셋입니다.');
        }

        return $this->successResponse($newProductHrefs, count($existingProductHrefs));
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
            ->pluck('productHref')
            ->toArray();
    }

    private function errorResponse($message)
    {
        return [
            'status' => false,
            'return' => ['message' => $message]
        ];
    }

    private function successResponse($newProductHrefs, $numDuplicated)
    {
        return [
            'status' => true,
            'return' => [
                'productHrefs' => $newProductHrefs,
                'numDuplicated' => $numDuplicated
            ]
        ];
    }
}
