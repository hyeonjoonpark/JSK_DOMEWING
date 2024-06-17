<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Partners\Products\UploadedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TrackSoldOutController extends Controller
{
    public function main(Request $request)
    {
        set_time_limit(0);
        $validator = Validator::make($request->all(), [
            'vendorId' => 'required|exists:product_search,vendor_id'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => '유효한 원청사를 선택해주세요.'
            ];
        }
        $vendorId = $request->vendorId;
        $products = DB::table('minewing_products')
            ->where('sellerID', $vendorId)
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
        $chunkedProducts = array_chunk($products, 500, false);
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
                $productFilePath = public_path('js/track-sold-out/products/error_' . $vendorEngName . '_' . $index . '.json');
                file_put_contents($productFilePath, json_encode($trackResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            } else {
                $soldOutProducts = array_merge($soldOutProducts, $trackResult['data']);
            }
            unlink($productFilePath);
        }
        $updateSoldOutProductsResult = $this->updateSoldOutProducts($vendorId, $soldOutProducts);
        if ($updateSoldOutProductsResult['status'] === false) {
            return $updateSoldOutProductsResult;
        }
        $dqc = new DeleteQueueController();
        $dqcStoreResult = $dqc->store($soldOutProducts);
        if (!$dqcStoreResult['status']) {
            $errors['dqcStoreResult'] = $dqcStoreResult;
        }
        return [
            'status' => true,
            'message' => '트랙윙을 성공적으로 완료했습니다.',
            'data' => [
                'soldOutProducts' => $soldOutProducts
            ],
            'error' => $errors
        ];
    }

    private function track($vendorEngName, $productFilePath, $username, $password)
    {
        $script = public_path('js/track-sold-out/' . $vendorEngName . '.js');
        $command = "node {$script} {$productFilePath} {$username} {$password}";
        try {
            exec($command, $output, $resultCode);
            $resultFilePath = public_path('js/track-sold-out/' . $vendorEngName . '_result.json');
            if (!file_exists($resultFilePath)) {
                return [
                    'status' => false,
                    'error' => json_decode($output[0])
                ];
            }
            $soldOutProducts = json_decode(file_get_contents($resultFilePath), true);
            unlink($resultFilePath);
            return [
                'status' => true,
                'data' => $soldOutProducts
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
            // 모든 제품의 isActive를 'Y'로 설정
            DB::table('minewing_products')
                ->where('sellerID', $vendorId)
                ->update(['isActive' => 'Y']);

            // soldOutProductIds에 포함된 제품의 isActive를 'N'로 설정
            DB::table('minewing_products')
                ->whereIn('id', $soldOutProductIds)
                ->update(['isActive' => 'N']);

            return ['status' => true];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '품절 상품들을 반영하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
}
