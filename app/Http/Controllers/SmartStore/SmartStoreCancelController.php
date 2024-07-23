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
        $isCancelOrder = $this->checkIsCancel($order);
        if ($isCancelOrder['status']) return $isCancelOrder;
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
    private function checkIsCancel($order)
    {
        $partnerOrder = DB::table('partner_orders')
            ->where('order_id', $order->id)
            ->first();
        $account = DB::table('smart_store_accounts as ssa')
            ->where('ssa.id', $partnerOrder->account_id)
            ->first();
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/query';
        $data = ['productOrderIds' => [$partnerOrder->product_order_number]];
        $controller = new SmartStoreApiController();
        $builderResponse =  $controller->builder($account, $contentType, $method, $url, $data); //조회해서 상태확인
        if (isset($builderResponse['data']['data'][0]['cancel'])) { //취소상태이면 취소 승인 진행
            return $this->acceptCancel($controller, $account, $contentType, $method, $partnerOrder->product_order_number); //취소 승인 api
        }
        return [
            'status' => false,
            'message' => '취소 요청 주문이 아닙니다.'
        ];
    }
    private function acceptCancel($controller, $account, $contentType, $method, $productOrderNumber)
    {
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/' . $productOrderNumber . '/claim/cancel/approve';
        $data = [
            'productOrderId' => $productOrderNumber
        ];
        return $controller->builder($account, $contentType, $method, $url, $data);
    }
}
