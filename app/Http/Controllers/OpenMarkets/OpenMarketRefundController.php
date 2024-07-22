<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangReturnController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OpenMarketRefundController extends Controller
{
    public function setAwaitingShipmentStatus($productOrderNumber)
    {
        $order = DB::table('orders')
            ->where('product_order_number', $productOrderNumber)
            ->where('requested', 'N')
            ->where('delivery_status', 'PENDING')
            ->where('type', 'REFUND')
            ->first();
        if (!$order) {
            return [
                'status' => false,
                'message' => '배송 대기 중으로 변경 가능한 주문이 아닙니다.',
            ];
        }
        $openMarket = DB::table('orders as o')
            ->join('partner_orders as po', 'o.id', '=', 'po.order_id')
            ->join('vendors as v', 'po.vendor_id', '=', 'v.id')
            ->where('o.product_order_number', $productOrderNumber)
            ->where('v.is_active', 'ACTIVE')
            ->select('v.*')
            ->first(['v.name_eng', 'v.name']);
        if ($openMarket && $openMarket->name_eng == 'coupang') { //일단은 쿠팡만 적용
            $method = 'call' . ucfirst($openMarket->name_eng) . 'CheckApi';
            $updateApiResult = $this->$method($productOrderNumber);
            if (!$updateApiResult['status']) {
                return $updateApiResult;
            }
        }
        $updated = DB::table('orders')
            ->where('product_order_number', $productOrderNumber)
            ->update(['requested' => 'Y']);
        if ($updated) {
            return response()->json([
                'status' => true,
                'message' => '주문 상태가 배송 대기 중으로 변경되었습니다.',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => '주문 상태 변경에 실패했습니다.',
            ]);
        }
    }
    public function saveRefundShipment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trackingNumber' => 'nullable|string|min:10|max:13',
            'deliveryCompanyId' => 'nullable|integer|exists:delivery_companies,id',
            'productOrderNumber' => 'required|string|exists:orders,product_order_number'
        ], [
            'trackingNumber.string' => '운송장 번호는 문자열이어야 합니다.',
            'trackingNumber.min' => '운송장 번호는 최소 10자여야 합니다.',
            'trackingNumber.max' => '운송장 번호는 최대 13자여야 합니다.',
            'deliveryCompanyId.exists' => '유효한 택배사를 선택해주세요.',
            'productOrderNumber.exists' => '유효한 주문이 아닙니다.',
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first(),
                'error' => $validator->errors(),
            ];
        }
        $productOrderNumber = $request->productOrderNumber;
        $order = DB::table('orders')
            ->where('product_order_number', $productOrderNumber)
            ->where('delivery_status', 'PENDING')
            ->where('type', 'REFUND')
            ->first();
        if ($order === null) {
            return [
                'status' => false,
                'message' => '취소되었거나, 이미 처리된 주문입니다.'
            ];
        }
        $deliveryCompanyId = $request->deliveryCompanyId;
        $trackingNumber = $request->trackingNumber;
        if ($trackingNumber && $deliveryCompanyId) {
            return $this->update($order->id, $deliveryCompanyId, $trackingNumber);
        } else {
            return $this->updateWithoutTracking($order->id);
        }
    }
    public function cancelRefund(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'remark' => 'required|string',
        ], [
            'remark.required' => '취소 사유는 필수 항목입니다.',
            'remark.string' => '취소 사유는 문자열이어야 합니다.',
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first(),
                'error' => $validator->errors(),
            ];
        }
        $remark = $request->remark;
        $productOrderNumber = $request->productOrderNumber;
        $order = DB::table('orders')
            ->where('product_order_number', $productOrderNumber)
            ->where('delivery_status', 'PENDING')
            ->where('type', 'REFUND')
            ->first();
        if ($order === null) {
            return [
                'status' => false,
                'message' => '취소되었거나, 이미 처리된 주문입니다.'
            ];
        }
        DB::table('orders')
            ->where('product_order_number', $productOrderNumber)
            ->update([
                'remark' => $remark,
                'requested' => 'N'
            ]);
        DB::table('wing_transactions')
            ->where('id', $order->wing_transaction_id)
            ->update([
                'status' => 'REJECTED',
                'remark' => $remark
            ]);
        return [
            'status' => true,
            'message' => '환불 요청이 취소되었습니다.'
        ];
    }
    public function acceptCancel($productOrderNumber, $remark)
    {
        if (!$remark) return [
            'status' => false,
            'message' => '취소사유는 필수입니다.',
        ];
        $order = DB::table('orders as o')
            ->join('wing_transactions as wt', 'wt.id', '=', 'o.wing_transaction_id')
            ->where('o.product_order_number', $productOrderNumber)
            ->where('o.delivery_status', 'PENDING')
            ->where('o.type', 'EXCHANGE')
            ->where('wt.status', 'PENDING')
            ->select('o.*')
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
    private function update($orderId, $deliveryCompanyId, $trackingNumber)
    {
        try {
            $order = DB::table('orders')
                ->where('id', $orderId)
                ->first();
            DB::table('orders')
                ->where('id', $orderId)
                ->update([
                    'tracking_number' => $trackingNumber,
                    'delivery_company_id' => $deliveryCompanyId,
                    'delivery_status' => 'COMPLETE',
                    'requested' => 'Y'
                ]);
            DB::table('wing_transactions')
                ->where('id', $order->wing_transaction_id)
                ->update([
                    'status' => 'APPROVED'
                ]);
            return [
                'status' => true,
                'message' => '송장번호 입력에 성공하였습니다'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '송장번호를 업데이트하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    private function updateWithoutTracking($orderId)
    {
        try {
            $order = DB::table('orders')
                ->where('id', $orderId)
                ->first();
            DB::table('orders')
                ->where('id', $orderId)
                ->update([
                    'delivery_status' => 'COMPLETE',
                ]);
            DB::table('wing_transactions')
                ->where('id', $order->wing_transaction_id)
                ->update([
                    'status' => 'APPROVED'
                ]);

            return [
                'status' => true,
                'message' => '송장번호, 택배사 없이 성공하였습니다'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    private function callCoupangCheckApi($productOrderNumber) //입고요청처리
    {
        $order = DB::table('orders')->where('product_order_number', $productOrderNumber)->first();
        $partnerOrder = DB::table('partner_orders')->where('order_id', $order->id)->first();
        $account = DB::table('coupang_accounts')->where('id', $partnerOrder->account_id)->first();
        $controller = new CoupangReturnController();
        $apiResult =  $controller->isCancelOrder($account, $partnerOrder->product_order_number);
        if (!$apiResult['status']) {
            DB::table('wing_transactions')->where('id', $order->wing_transaction_id)->update(['status' => 'REJECTED']);
            return [
                'status' => false,
                'message' => '고객이 반품접수를 취소하여 반품주문 취소처리하였습니다.',
                'data' => $apiResult
            ];
        }
        $confirmApiResult = $controller->confirmReturnReceipt($account, $partnerOrder->product_order_number);
        if (!$confirmApiResult['status']) {
            return [
                'status' => false,
                'message' => '쿠팡 반품 주문 입고 요청에 실패하였습니다. 관리자에게 문의해주세요.',
                'data' => $confirmApiResult
            ];
        }
        return [
            'status' => true
        ];
    }
}
