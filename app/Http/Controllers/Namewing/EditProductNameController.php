<?php

namespace App\Http\Controllers\Namewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EditProductNameController extends Controller
{
    public function index(Request $request)
    {
        $productCode = $request->productCode;
        $newProductName = $request->newProductName;
        return $this->updateNewProductName($productCode, $newProductName);
    }
    private function updateNewProductName($productCode, $newProductName)
    {
        try {
            DB::table('minewing_products')
                ->where('productCode', $productCode)
                ->update([
                    'productName' => $newProductName
                ]);
            return [
                'status' => true,
                'return' => '"상품명을 성공적으로 수정했습니다."'
            ];
        } catch (\Exception $e) {
            return [
                'status' => true,
                'return' => '"상품명 수정에 실패했습니다. 기술자에게 문의해주십시오."',
                'error' => $e->getMessage()
            ];
        }
    }
}
