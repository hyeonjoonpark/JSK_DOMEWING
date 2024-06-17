<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Partners\Products\UploadedController;
use App\Models\DeleteQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeleteQueueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * @param array $productIds
     * @return array
     */
    public function store(array $productIds)
    {
        $existingProductIds = DeleteQueue::whereIn('product_id', $productIds)
            ->pluck('product_id')
            ->toArray();
        $newProductIds = array_diff($productIds, $existingProductIds);
        $data = array_map(function ($productId) {
            return ['product_id' => $productId];
        }, $newProductIds);
        if (!empty($data)) {
            try {
                DeleteQueue::insert($data);
                return [
                    'status' => true,
                    'message' => '해당 상품들을 성공적으로 대기열에 기록했습니다. 매일 오후 7시에 파트너스 상품 삭제 프로토콜이 진행됩니다.'
                ];
            } catch (\Exception $e) {
                return [
                    'status' => false,
                    'message' => '해당 상품들을 대기열에 기록하는 과정에서 오류가 발생했습니다.',
                    'error' => $e->getMessage()
                ];
            }
        }
        return [
            'status' => false,
            'message' => '이미 대기열에 등록된 상품셋입니다.'
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(DeleteQueue $deleteQueue)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DeleteQueue $deleteQueue)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DeleteQueue $deleteQueue)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        // UploadedController의 인스턴스 생성
        $uc = new UploadedController();

        // 삭제 대기열에서 모든 product_id를 가져옴
        $deleteQueues = DeleteQueue::pluck('product_id')->toArray();

        // 활성 상태인 모든 벤더(오픈 마켓)를 가져옴
        $openMarkets = DB::table('vendors')
            ->where('type', 'OPEN_MARKET')
            ->where('is_active', 'ACTIVE')
            ->get(['id', 'name_eng']);

        // 삭제 결과를 저장할 경로 설정
        $deleteQueueResultsPath = public_path('assets/json/delete-queue-error-logs/');
        if (!is_dir($deleteQueueResultsPath)) {
            mkdir($deleteQueueResultsPath, 0755, true);
        }

        // 각 오픈 마켓에 대해
        foreach ($openMarkets as $openMarket) {
            $table = $openMarket->name_eng . '_uploaded_products';

            // 해당 마켓의 업로드된 제품 테이블에서 원본 제품 번호를 가져옴
            $originProductsNo = DB::table($table)
                ->whereIn('product_id', $deleteQueues)
                ->pluck('origin_product_no')
                ->toArray();

            // 삭제 요청 생성
            $ucDeleteRequest = new Request([
                'vendorId' => $openMarket->id,
                'originProductsNo' => $originProductsNo
            ]);

            // 삭제 요청 실행 및 결과 저장
            $ucDeleteResult = $uc->delete($ucDeleteRequest);
            if (!$ucDeleteResult['status']) {
                file_put_contents($deleteQueueResultsPath . $openMarket->name_eng . '_' . date('YmdHis') . '.json', json_encode($ucDeleteResult));
            }

            DB::table($table)
                ->whereIn('product_id', $deleteQueues)
                ->update([
                    'is_active' => 'N'
                ]);
        }

        // 데이터베이스 트랜잭션 시작
        DB::beginTransaction();
        try {
            // minewing_products 테이블에서 삭제 대기열에 있는 제품의 isActive 상태를 'N'으로 업데이트
            DB::table('minewing_products')
                ->whereIn('id', $deleteQueues)
                ->update([
                    'isActive' => 'N'
                ]);

            // 삭제 대기열에서 해당 제품 ID를 삭제
            DeleteQueue::whereIn('product_id', $deleteQueues)->delete();

            // 트랜잭션 커밋
            DB::commit();
            return [
                'status' => true
            ];
        } catch (\Exception $e) {
            // 오류 발생 시 트랜잭션 롤백
            DB::rollBack();
            return [
                'status' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
