<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SmartStore\SmartStoreShipmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpenMarketShipmentController extends Controller
{
    public function index(Request $request)
    {
        $productOrderNumber = $request->input('productOrderNumber');
        $order = $this->getOrder($productOrderNumber);
        $partnerOrder = $this->getPartnerOrder($order->id);
        $vendor = $this->getOpenMarket($partnerOrder);
        $method = 'call' . ucfirst($vendor->name_eng) . 'ShipmentApi';
        $result = call_user_func([$this, $method], $request);
        return $result;
    }
    private function getOrder($productOrderNumber)
    {
        return DB::table('orders as o')
            ->where('o.product_order_number', $productOrderNumber)
            ->first();
    }
    private function getPartnerOrder($orderId)
    {
        return DB::table('partner_orders as po')
            ->where('po.order_id', $orderId)
            ->first();
    }
    private function getOpenMarket($partnerOrder)
    {
        return DB::table('vendors as v')
            ->where('v.id', $partnerOrder->vendor_id)
            ->where('v.is_active', 'ACTIVE')
            ->first();
    }
    private function callSmart_storeShipmentApi($request)
    {
        $controller = new SmartStoreShipmentController();
        return $controller->index($request);
    }
}
