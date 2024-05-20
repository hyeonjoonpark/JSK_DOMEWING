<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SmartStore\CoupangShipmentController;
use App\Http\Controllers\SmartStore\SmartStoreShipmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OpenMarketShipmentController extends Controller
{
    public function saveShipment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trackingNumber' => 'required|numeric',
            'deliveryCompanyId' => 'required',
        ], [
            'trackingNumber.required' => '송장번호 입력 후 확인 부탁드립니다.',
            'trackingNumber.numeric' => '송장번호는 숫자만 입력 가능합니다.',
            'deliveryCompanyId.required' => '택배사를 지정 후 확인 부탁드립니다.',
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => '기입 정보가 올바르지 않습니다.',
                'error' => $validator->errors(),
            ];
        }
        $productOrderNumber = $request->input('productOrderNumber');
        $order = $this->getOrder($productOrderNumber);
        if (!$order) return [
            'status' => false,
            'message' => '이미 취소되었거나 유효한 주문이 아닙니다.',
        ];
        $vendor = $this->getVendorByProductOrderNumber($productOrderNumber);
        if ($vendor) {
            $method = 'call' . ucfirst($vendor->name_eng) . 'ShipmentApi';
            return $this->$method($request);
        } else {
            $checkDeliveryCompnayId = DB::table('delivery_companies as dc')
                ->where('dc.id', $request->deliveryCompanyId)
                ->exists();
            if ($checkDeliveryCompnayId) {
                DB::table('orders as o')
                    ->where('o.product_order_number', $productOrderNumber)
                    ->update([
                        'delivery_company_id' => $request->input('deliveryCompanyId'),
                        'tracking_number' => $request->input('trackingNumber'),
                        'delivery_status' => 'COMPLETE'
                    ]);
                return [
                    'status' => true,
                    'message' => '배송 정보가 성공적으로 업데이트되었습니다.',
                ];
            }
        }
    }

    private function getVendorByProductOrderNumber($productOrderNumber)
    {
        return DB::table('orders as o')
            ->join('partner_orders as po', 'o.id', '=', 'po.order_id')
            ->join('vendors as v', 'po.vendor_id', '=', 'v.id')
            ->where('o.product_order_number', $productOrderNumber)
            ->where('v.is_active', 'ACTIVE')
            ->select('v.*')
            ->first();
    }
    private function getOrder($productOrderNumber)
    {
        return DB::table('orders as o')
            ->where('product_order_number', $productOrderNumber)
            ->where('delivery_status', 'PENDING')
            ->where('type', 'PAID')
            ->first();
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
