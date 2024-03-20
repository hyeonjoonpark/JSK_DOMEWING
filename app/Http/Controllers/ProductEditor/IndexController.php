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
    public function inactiveProducts($productCodes)
    {
        try {
            DB::table('minewing_products')
                ->whereIn('productCode', $productCodes)
                ->update([
                    'isActive' => 'N',
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
