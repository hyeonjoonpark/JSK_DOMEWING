<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SmartStore\CoupangShipmentController;
use App\Http\Controllers\SmartStore\SmartStoreShipmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpenMarketShipmentController extends Controller
{
    public function index(Request $request)
    {
        $productOrderNumber = $request->input('productOrderNumber');
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
                return response()->json([
                    'status' => true,
                    'message' => '배송 정보가 성공적으로 업데이트되었습니다.',
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => '유효하지 않은 배송 회사 ID입니다.',
                ], 400);
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
