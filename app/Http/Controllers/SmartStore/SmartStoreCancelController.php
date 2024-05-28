<?php

namespace App\Http\Controllers\SmartStore;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SmartStore\SmartStoreApiController;
use Illuminate\Support\Facades\DB;

class SmartStoreCancelController extends Controller
{
    private $ssac;
    public function __construct()
    {
        $this->ssac = new SmartStoreApiController();
    }
    public function index($productOrderNumber)
    {
        $order = DB::table('orders as o')
            ->where('o.product_order_number', $productOrderNumber)
            ->where('type', 'PAID')
            ->first();
        $partnerOrder = DB::table('partner_orders as po')
            ->where('po.order_id', $order->id)
            ->first();
        $account = DB::table('smart_store_accounts')
            ->where('id', $partnerOrder->account_id)
            ->first();
        return $this->cancelOrder($account, $partnerOrder->product_order_number);
    }
    public function cancelOrder($account, $productOrderId)
    {
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/cancel/request';
        $data = ['cancelReason' => 'SOLD_OUT'];
        $response = $this->ssac->builder($account, $contentType, $method, $url, $data);
        if (!$response['status']) return [
            'status' => false,
            'message' => '주문취소에 실패하였습니다.',
            'error' => $response['error']
        ];
        return [
            'status' => true,
            'message' => '주문취소에 성공하였습니다.',
            'data' => $response
        ];
    }
}
