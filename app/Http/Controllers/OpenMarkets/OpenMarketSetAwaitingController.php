<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangShipmentController;
use App\Http\Controllers\SmartStore\SmartStoreShipmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OpenMarketSetAwaitingController extends Controller
{
    public function setAwaitingShipmentStatus($productOrderNumber)
    {
        $order = DB::table('orders')
            ->where('product_order_number', $productOrderNumber)
            ->where('requested', 'N')
            ->where('delivery_status', 'PENDING')
            ->whereNotIn('type', ['CANCELLED'])
            ->first();
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
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => '배송 대기 중으로 변경 가능한 주문이 아닙니다.',
            ]);
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
}
