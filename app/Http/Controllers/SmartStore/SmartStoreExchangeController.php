<?php

namespace App\Http\Controllers\SmartStore;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SmartStore\SmartStoreApiController;
use Illuminate\Support\Facades\DB;

class SmartStoreExchangeController extends Controller
{
    private $ssac;
    public function __construct()
    {
        $this->ssac = new SmartStoreApiController();
    }
    public function index()
    {
    }
    private function completeExchangePickup($account, $productOrderId) // 교환 수거 완료
    {
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/exchange/collect/approve';
        $data = [];
        return $this->ssac->builder($account, $contentType, $method, $url, $data);
    }
    private function reshipExchange($account, $productOrderId, $deliveryCompany, $deliveryTrackingNumber) // 교환 재배송 처리
    {
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/exchange/dispatch';
        $data = [
            'reDeliveryMethod' => 'DELIVERY', //택배만 하기 때문
            'reDeliveryCompany' => $deliveryCompany,
            'reDeliveryTrackingNumber' => $deliveryTrackingNumber,
        ];
        return $this->ssac->builder($account, $contentType, $method, $url, $data);
    }
    private function rejectExchangeRequest($account, $productOrderId, $rejectExchangeReason) // 교환 거부(재배송) 처리
    {
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/exchange/reject';
        $data = [
            'rejectExchangeReason' => $rejectExchangeReason
        ];
        return $this->ssac->builder($account, $contentType, $method, $url, $data);
    }
}
