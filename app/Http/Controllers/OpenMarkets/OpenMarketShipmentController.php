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
        $vendor = $this->getVendorByProductOrderNumber($productOrderNumber);
        $method = 'call' . ucfirst($vendor->name_eng) . 'ShipmentApi';
        return $this->$method($request);
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
}
