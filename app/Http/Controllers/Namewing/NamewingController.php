<?php

namespace App\Http\Controllers\Namewing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\NameController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
    public function power($vendorId = null)
    {
        try {
            DB::transaction(function () use ($vendorId) {
                if ($vendorId === null) {
                    DB::update("
                        UPDATE minewing_products
                        SET isActive = 'N'
                        WHERE id IN (
                            SELECT id
                            FROM (
                                SELECT
                                    id,
                                    productName,
                                    ROW_NUMBER() OVER(PARTITION BY productName ORDER BY productPrice ASC) AS rn
                                FROM minewing_products
                                WHERE isActive = 'Y'
                            ) AS ranked_products
                            WHERE rn > 1
                    );
                ");
                } else {
                    DB::update("
                        UPDATE minewing_products
                        SET isActive = 'N'
                        WHERE id IN (
                            SELECT id
                            FROM (
                                SELECT
                                    id,
                                    productName,
                                    ROW_NUMBER() OVER(PARTITION BY productName ORDER BY productPrice ASC) AS rn
                                FROM minewing_products
                                WHERE isActive = 'Y' AND sellerID = ?
                            ) AS ranked_products
                            WHERE rn > 1
                    );
                ", [$vendorId]);
                }
            });

            return [
                'status' => true,
                'message' => "파워 네임윙을 성공적으로 처리했습니다. 반드시 상품군들을 확인해주세요."
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => "파워 네임윙 가동 중 에러가 발생했습니다. 다음에 다시 시도해주십시오.",
                'error' => $e->getMessage()
            ];
        }
    }
    public function multiEdit(Request $request)
    {
        $namewings = $request->namewings;
        $nameController = new NameController();
        foreach ($namewings as $namewing) {
            $productCode = $namewing['productCode'];
            $productName = $nameController->index($namewing['productName']);
            $result = $this->update($productCode, $productName);
            if ($result['status'] === false) {
                return $result;
            }
        }
        return $result;
    }
    private function update($productCode, $productName)
    {
        try {
            DB::table('minewing_products')
                ->where('productCode', $productCode)
                ->update([
                    'productName' => $productName
                ]);
            return [
                'status' => true,
                'message' => '네임윙을 성공적으로 진행했습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '네임윙 진행 중 에러가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
}
