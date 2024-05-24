<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangShipmentController;
use App\Http\Controllers\SmartStore\SmartStoreShipmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OpenMarketShipmentController extends Controller
{
    public function saveShipment(Request $request)
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
            ->where('type', 'PAID')
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
        if ($openMarket) {
            $method = 'call' . ucfirst($openMarket->name_eng) . 'ShipmentApi';
            $updateApiResult = $this->$method($request);
            return $updateApiResult;
            if ($updateApiResult['status'] === false) {
                return $updateApiResult;
            }
        }
        return $this->update($order->id, $deliveryCompanyId, $trackingNumber);
    }
    private function update($orderId, $deliveryCompanyId, $trackingNumber)
    {
        try {
            DB::table('orders')
                ->where('id', $orderId)
                ->update([
                    'tracking_number' => $trackingNumber,
                    'delivery_company_id' => $deliveryCompanyId,
                    'delivery_status' => 'COMPLETE',
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
    private function callSmart_storeShipmentApi(Request $request)
    {
        $controller = new SmartStoreShipmentController();
        return $controller->index($request);
    }
    private function callCoupangShipmentApi(Request $request)
    {
        $controller = new CoupangShipmentController();
        return $controller->index($request);
    }
}
