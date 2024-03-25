<?php

namespace App\Http\Controllers\ProductEditor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    /**
     * @param array $productCodes
     * @return object
     */
    public function inactiveProducts($productCodes, $type)
    {
        try {
            $typeStr = null;
            if ($type === 'sold-out') {
                $typeStr = 'N';
            }
            if ($type === 'restock') {
                $typeStr = 'Y';
            }
            if ($typeStr === null) {
                return [
                    'status' => false,
                    'return' => '올바른 접근이 아닙니다.'
                ];
            }
            DB::table('minewing_products')
                ->whereIn('productCode', $productCodes)
                ->update([
                    'isActive' => $typeStr,
                    'updatedAt' => now()
                ]);
            return [
                'status' => true
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage()
            ];
        }
    }
}
