<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangExchangeController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OpenMarketExchangeController extends Controller
{
    public function setAwaitingShipmentStatus($productOrderNumber)
    {
        $order = DB::table('orders')
            ->where('product_order_number', $productOrderNumber)
            ->where('requested', 'N')
            ->where('delivery_status', 'PENDING')
            ->where('type', 'EXCHANGE')
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
                'message' => '주문 상태가 교환 대기 중으로 변경되었습니다.',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => '주문 상태 변경에 실패했습니다.',
            ]);
        }
    }
    public function saveExchangeShipment(Request $request)
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
            ->where('type', 'EXCHANGE')
            ->first();
        $deliveryCompanyCode = DB::table('delivery_companies as dc')
            ->where('dc.id', $deliveryCompanyId)
            ->first();
        if ($order === null) {
            return [
                'status' => false,
                'message' => '취소되었거나, 이미 처리된 주문입니다.'
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
            // if ($openMarket) {
            $method = 'call' . ucfirst($openMarket->name_eng) . 'ShipmentApi';
            $updateApiResult = $this->$method($productOrderNumber, $deliveryCompanyCode, $trackingNumber);
            if ($updateApiResult['status'] === false) {
                return $updateApiResult;
            }
        }
        return $this->update($order->id, $deliveryCompanyId, $trackingNumber);
    }
    public function cancelExchange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'remark' => 'required|string',
            'productOrderNumber' => 'required|string|exists:orders,product_order_number'
        ], [
            'remark.required' => '취소 사유는 필수 항목입니다.',
            'remark.string' => '취소 사유는 문자열이어야 합니다.',
            'productOrderNumber' => '유효한 주문이 아닙니다.',
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
            ->where('type', 'EXCHANGE')
            ->first();
        if ($order === null) {
            return [
                'status' => false,
                'message' => '취소되었거나, 이미 처리된 주문입니다.'
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
            // if ($openMarket) {
            $method = 'call' . ucfirst($openMarket->name_eng) . 'CancelApi';
            $updateApiResult = $this->$method($productOrderNumber);
            if ($updateApiResult['status'] === false) {
                return $updateApiResult;
            }
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
            'message' => '교환 요청이 취소되었습니다.'
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
                    'requested' => 'Y'
                ]);
            DB::table('wing_transactions')
                ->where('id', $order->wing_transaction_id)
                ->update([
                    'status' => 'APPROVED'
                ]);
            return [
                'status' => true,
                'message' => '교환 송장번호 입력에 성공하였습니다'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '송장번호를 업데이트하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    private function callCoupangCheckApi($productOrderNumber) //입고요청처리
    {
        $order = DB::table('orders')->where('product_order_number', $productOrderNumber)->first();
        $partnerOrder = DB::table('partner_orders')->where('order_id', $order->id)->first();
        $account = DB::table('coupang_accounts')->where('id', $partnerOrder->account_id)->first();
        $controller = new CoupangExchangeController();
        $apiResult =  $controller->isCancelOrder($account, $partnerOrder->product_order_number);
        if ($apiResult['status']) {
            DB::table('wing_transactions')->where('id', $order->wing_transaction_id)->update(['status' => 'REJECTED']);
            return [
                'status' => false,
                'message' => '고객이 교환접수를 취소하여 교환주문 취소처리하였습니다.',
                'data' => $apiResult
            ];
        }
        $confirmApiResult = $controller->checkInExchangeRequest($account, $partnerOrder->product_order_number);
        if (!$confirmApiResult['status']) {
            return [
                'status' => false,
                'message' => '쿠팡 교환 주문 입고 요청에 실패하였습니다. 관리자에게 문의해주세요.',
                'data' => $confirmApiResult
            ];
        }
        return [
            'status' => true
        ];
    }
    private function callCoupangShipmentApi($productOrderNumber, $goodsDeliveryCode, $invoiceNumber)
    {
        $order = DB::table('orders')->where('product_order_number', $productOrderNumber)->first();
        $partnerOrder = DB::table('partner_orders')->where('order_id', $order->id)->first();
        $account = DB::table('coupang_accounts')->where('id', $partnerOrder->account_id)->first();
        $controller = new CoupangExchangeController();
        $confirmApiResult = $controller->exchangeShipment($account, $partnerOrder, $goodsDeliveryCode->coupang, $invoiceNumber);
        if (!$confirmApiResult['status']) {
            return [
                'status' => false,
                'message' => '쿠팡 교환 주문 송장번호 입력에 실패하였습니다. 관리자에게 문의해주세요.',
                'data' => $confirmApiResult
            ];
        }
        return [
            'status' => true
        ];
    }
    private function callCoupangCancelApi($productOrderNumber)
    {
        $order = DB::table('orders')->where('product_order_number', $productOrderNumber)->first();
        $partnerOrder = DB::table('partner_orders')->where('order_id', $order->id)->first();
        $account = DB::table('coupang_accounts')->where('id', $partnerOrder->account_id)->first();
        $controller = new CoupangExchangeController();
        $rejectedExchange = $controller->rejectExchangeRequest($account, $partnerOrder->product_order_number);
        if (!$rejectedExchange['status']) {
            return [
                'status' => false,
                'message' => '쿠팡 교환 주문 취소에 실패하였습니다. 관리자에게 문의해주세요.',
                'data' => $rejectedExchange
            ];
        }
        return [
            'status' => true
        ];
    }
}
