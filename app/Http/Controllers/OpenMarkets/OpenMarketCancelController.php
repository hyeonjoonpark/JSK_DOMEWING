<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangCancelController;
use App\Http\Controllers\OpenMarkets\St11\St11CancelController;
use App\Http\Controllers\SmartStore\SmartStoreCancelController;
use Illuminate\Support\Facades\DB;

class OpenMarketCancelController extends Controller
{
    public function cancelOrder($productOrderNumber, $remark)
    {
        if (!$remark) return [
            'status' => false,
            'message' => '취소사유는 필수입니다.',
        ];
        $order = DB::table('orders as o') //주문내역가지고 작업하기전에 유효한지 확인하고 없으면 return
            ->where('product_order_number', $productOrderNumber)
            ->where('delivery_status', 'PENDING')
            ->where('type', '!=', 'CANCELLED')
            ->first();
        if (!$order) return [
            'status' => false,
            'message' => '이미 취소되었거나 유효한 주문이 아닙니다.',
        ];
        $partnerOrder = DB::table('partner_orders as po') //해당 주문의 partner_order 테이블 조회
            ->where('order_id', $order->id)
            ->first();
        if ($partnerOrder) {
            $vendor = DB::table('vendors as v') //해당 주문의 오픈마켓이 어디인지 조회
                ->where('v.id', $partnerOrder->vendor_id)
                ->where('is_active', 'ACTIVE')
                ->first();
            $vendorEngName = $vendor->name_eng;
            $method = 'call' . ucfirst($vendorEngName) . 'CancelApi'; //해당 오픈마켓의 api 호출을 위한 메소드 작성
            $apiResult = call_user_func([$this, $method], $productOrderNumber); //api 결과 저장
            if (!$apiResult['status']) return [
                'status' => false,
                'message' => '오픈마켓 주문취소 과정에서 오류가 발생하였습니다.',
                'data' => $apiResult
            ];
        }

        DB::beginTransaction();
        try {
            // orders 테이블 업데이트
            DB::table('orders')
                ->where('product_order_number', $productOrderNumber)
                ->where('delivery_status', 'PENDING')
                ->update([
                    'remark' => $remark,
                    'requested' => 'N'
                ]);
            DB::table('wing_transactions')
                ->where('id', $order->wing_transaction_id)
                ->update([
                    'status' => 'REJECTED'
                ]);
            // 트랜잭션 커밋
            DB::commit();
            return [
                'status' => true,
                'message' => '주문이 성공적으로 취소되었습니다.',
            ];
        } catch (\Exception $e) {
            // 트랜잭션 롤백
            DB::rollBack();
            return [
                'status' => false,
                'message' => '주문 취소 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ];
        }
    }
    public function acceptCancel($productOrderNumber, $remark)
    {
        if (!$remark) return [
            'status' => false,
            'message' => '취소사유는 필수입니다.',
        ];
        $order = DB::table('orders as o') //주문내역가지고 작업하기전에 유효한지 확인하고 없으면 return
            ->where('product_order_number', $productOrderNumber)
            ->where('delivery_status', 'PENDING')
            ->where('type', '!=', 'CANCELLED')
            ->first();
        if (!$order) return [
            'status' => false,
            'message' => '이미 취소되었거나 유효한 주문이 아닙니다.',
        ];
        DB::beginTransaction();
        try {
            // orders 테이블 업데이트
            DB::table('orders')
                ->where('product_order_number', $productOrderNumber)
                ->where('delivery_status', 'PENDING')
                ->update([
                    'remark' => $remark,
                    'requested' => 'N'
                ]);
            DB::table('wing_transactions')
                ->where('id', $order->wing_transaction_id)
                ->update([
                    'status' => 'REJECTED'
                ]);
            // 트랜잭션 커밋
            DB::commit();
            return [
                'status' => true,
                'message' => '주문이 성공적으로 취소되었습니다.',
            ];
        } catch (\Exception $e) {
            // 트랜잭션 롤백
            DB::rollBack();
            return [
                'status' => false,
                'message' => '주문 취소 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ];
        }
    }
    private function callSmart_storeCancelApi($productOrderNumber)
    {
        $controller = new SmartStoreCancelController();
        return $controller->index($productOrderNumber);
    }
    private function callCoupangCancelApi($productOrderNumber)
    {
        $controller = new CoupangCancelController();
        return $controller->index($productOrderNumber);
    }
    private function callSt11CancelApi($productOrderNumber)
    {
        $controller = new St11CancelController();
        return $controller->index($productOrderNumber);
    }
}
