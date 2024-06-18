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
        $data = ['cancelReason' => 'DELAYED_DELIVERY'];
        /*
        INTENT_CHANGED	구매 의사 취소
        COLOR_AND_SIZE	색상 및 사이즈 변경
        WRONG_ORDER	다른 상품 잘못 주문
        PRODUCT_UNSATISFIED	서비스 불만족
        DELAYED_DELIVERY	배송 지연
        SOLD_OUT	상품 품절
        INCORRECT_INFO	상품 정보 상이
        */
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
