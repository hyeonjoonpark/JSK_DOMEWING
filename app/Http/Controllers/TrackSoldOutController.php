<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TrackSoldOutController extends Controller
{
    public function main(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendorId' => 'required|exists:product_search,vendor_id'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => '유효한 원청사를 선택해주세요.'
            ], 200);
        }
        $vendorId = $request->vendorId;

        $products = DB::table('minewing_products')
            ->where('sellerID', $vendorId)
            ->limit(30)
            ->get([
                'id',
                'productHref',
                'hasOption',
                'productDetail'
            ])
            ->toArray();
        $vendorEngName = DB::table('vendors')
            ->where('id', $vendorId)
            ->value('name_eng');
        $account = DB::table('accounts')
            ->where('vendor_id', $vendorId)
            ->select([
                'username',
                'password'
            ])
            ->first();
        $username = $account->username;
        $password = $account->password;
        $chunkedProducts = array_chunk($products, 10, false);
        $productFilePath = public_path('js/track-sold-out/products/');
        if (!is_dir($productFilePath)) {
            mkdir($productFilePath, 0755, true);
        }
        $soldOutProducts = [];
        foreach ($chunkedProducts as $i => $products) {
            $index = $i + 1;
            $productFilePath = public_path('js/track-sold-out/products/' . $vendorEngName . '_' . $index . '.json');
            file_put_contents($productFilePath, json_encode($products));
            $trackResult = $this->track($vendorEngName, $productFilePath, $username, $password);
            if ($trackResult['status'] === false) {
                return $trackResult;
            }
            $soldOutProducts = array_merge($soldOutProducts, $trackResult['data']);
            unlink($productFilePath);
        }
        $updateSoldOutProductsResult = $this->updateSoldOutProducts($vendorId, $soldOutProducts);
        if ($updateSoldOutProductsResult['status'] === false) {
            return $updateSoldOutProductsResult;
        }
        return [
            'status' => true,
            'message' => '해당 원청사의 품절 추적 프로토콜을 성공적으로 수행했습니다.',
            'data' => [
                'soldOutProducts' => $soldOutProducts
            ]
        ];
    }

    private function track($vendorEngName, $productFilePath, $username, $password)
    {
        $script = public_path('js/track-sold-out/' . $vendorEngName . '.js');
        $command = "node {$script} {$productFilePath} {$username} $password";
        try {
            exec($command, $output, $resultCode);
            $soldOutProducts = json_decode($output[0], true);
            return [
                'status' => true,
                'data' => [
                    'soldOutProducts' => $soldOutProducts
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '데이터를 전송받는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }


    private function updateSoldOutProducts($vendorId, array $soldOutProductIds)
    {
        try {
            // 먼저 모든 제품의 isActive를 'Y'로 설정
            DB::table('minewing_products')
                ->where('sellerID', $vendorId)
                ->update([
                    'isActive' => 'Y'
                ]);

            // soldOutProductIds에 포함된 제품의 isActive를 'N'로 설정
            DB::table('minewing_products')
                ->whereIn('id', $soldOutProductIds)
                ->update([
                    'isActive' => 'N'
                ]);

            return [
                'status' => true
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '품절 상품들을 반영하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }

    private function activeVendorAllProducts($vendorId)
    {
        try {
            $productHrefs = DB::table('minewing_products')
                ->where('sellerID', $vendorId)
                ->update([
                    'isActive' => 'Y'
                ]);
            return [
                'status' => true,
                'data' => [
                    'productHrefs' => $productHrefs
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '원청사의 모든 상품들을 재입고 처리하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
}
