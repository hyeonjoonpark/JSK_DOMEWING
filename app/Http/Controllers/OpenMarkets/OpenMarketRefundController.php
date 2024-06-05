<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OpenMarketRefundController extends Controller
{
    public function saveRefundShipment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trackingNumber' => 'required|string|min:10|max:13',
            'deliveryCompanyId' => 'required|integer|exists:delivery_companies,id',
            'productOrderNumber' => 'required|string|exists:orders,product_order_number'
        ], [
            'trackingNumber.required' => '운송장 번호는 필수 항목입니다.',
            'trackingNumber.string' => '운송장 번호는 문자열이어야 합니다.',
            'trackingNumber.min' => '운송장 번호는 최소 10자여야 합니다.',
            'trackingNumber.max' => '운송장 번호는 최대 13자여야 합니다.',
            'deliveryCompanyId' => '유효한 택배사를 선택해주세요.',
            'productOrderNumber' => '유효한 주문이 아닙니다.',
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first(),
                'error' => $validator->errors(),
            ];
        }
        $trackingNumber = $request->trackingNumber;
        $deliveryCompanyId = $request->deliveryCompanyId;
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
        // $openMarket = DB::table('orders as o')
        //     ->join('partner_orders as po', 'o.id', '=', 'po.order_id')
        //     ->join('vendors as v', 'po.vendor_id', '=', 'v.id')
        //     ->where('o.product_order_number', $productOrderNumber)
        //     ->where('v.is_active', 'ACTIVE')
        //     ->select('v.*')
        //     ->first(['v.name_eng', 'v.name']);
        // if ($openMarket) {
        //     $method = 'call' . ucfirst($openMarket->name_eng) . 'ShipmentApi';
        //     $updateApiResult = $this->$method($request);
        //     if ($updateApiResult['status'] === false) {
        //         return $updateApiResult;
        //     }
        // }
        return $this->update($order->id, $deliveryCompanyId, $trackingNumber);
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
                'type' => 'CANCELLED',
                'remark' => $remark
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
}
