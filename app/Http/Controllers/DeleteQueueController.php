<?php

namespace App\Http\Controllers;

use App\Models\DeleteQueue;
use Illuminate\Http\Request;

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
                    'message' => '해당 상품들을 성공적으로 대기열에 기록했습니다. 매일 오후 7시에 상품 삭제 프로토콜이 진행됩니다.'
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
    public function destroy(DeleteQueue $deleteQueue)
    {
        //
    }
}
