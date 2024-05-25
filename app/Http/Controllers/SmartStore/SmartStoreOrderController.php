<?php

namespace App\Http\Controllers\SmartStore;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SmartStore\SmartStoreApiController;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SmartStoreOrderController extends Controller
{
    private $ssac;
    public function __construct()
    {
        $this->ssac = new SmartStoreApiController();
    }
    public function index($id = null, $start = null, $end = null)
    {
        if ($id == null) {
            $id = Auth::guard('partner')->id();
        }
        $accounts = $this->getAccount($id);
        if (!$accounts) {
            return false;
        }
        $allOrderDetails = [];
        foreach ($accounts as $account) {
            $orderList = $this->getOrderList($account, $start, $end);
            $orderIds = $this->getOrderIds($orderList);
            $orderDetails = $this->getOrderDetails($account, $orderIds);
            if (isset($orderDetails['error'])) {
                continue; // 오류가 있으면 다음 계정으로 넘어감
            }
            $response = $this->confirm($account, $orderIds);
            $allOrderDetails = array_merge($allOrderDetails, $orderDetails);
        }

        return  $allOrderDetails;
    }
    private function getOrderList($account, $start = null, $end = null)
    {
        $contentType = 'application/json';
        $method = 'GET';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/last-changed-statuses';
        $startDate = $start ? new DateTime($start) : new DateTime('now - 4 days');
        $endDate = $end ? new DateTime($end) : new DateTime('now');
        $responses = [];
        for ($date = $startDate; $date <= $endDate; $date->modify('+1 day')) {
            $formattedDate = $this->convertDateFormat($date->format('Y-m-d'));
            $data = ['lastChangedFrom' => $formattedDate];
            $response = $this->ssac->builder($account, $contentType, $method, $url, $data);
            $responses[$formattedDate] = $response;
        }
        return $responses;
    }
    private function confirm($account, $productOrderIds)
    {
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/confirm';
        $data = ['productOrderIds' => $productOrderIds];
        return $this->ssac->builder($account, $contentType, $method, $url, $data);
    }
    private function convertDateFormat($inputDate)
    {
        $date = new DateTime($inputDate, new DateTimeZone('Asia/Seoul'));
        return $date->format('Y-m-d\TH:i:s.vP');
    }
    private function getOrderIds($response)
    {
        $orderIds = [];
        foreach ($response as $dateKey => $dateData) {
            if (isset($dateData['data']['data']['lastChangeStatuses'])) {
                foreach ($dateData['data']['data']['lastChangeStatuses'] as $status) {
                    if (isset($status['productOrderId'])) {
                        $orderIds[] = $status['productOrderId'];
                    }
                }
            }
        }
        return $orderIds;
    }
    private function getOrderDetails($account, $productOrderIds)
    {
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/query';
        $data = ['productOrderIds' => $productOrderIds];
        $response = $this->ssac->builder($account, $contentType, $method, $url, $data);
        if (!isset($response['data']['data'])) {
            return ['error' => '응답 데이터가 올바르지 않습니다.'];
        }
        $statusMap = [
            'PAYMENT_WAITING' => '결제대기',
            'PAYED' => '결제완료',
            'DELIVERING' => '배송중',
            'DELIVERED' => '배송완료',
            'EXCHANGED' => '교환',
            'CANCELED' => '취소',
            'RETURNED' => '반품',
            'CANCELED_BY_NOPAYMENT' => '미결제취소',
            'PURCHASE_DECIDED' => '구매확정',
        ];
        $formattedResponse = array_map(function ($item) use ($statusMap, $account) {
            $shippingAddress = isset($item['productOrder']['shippingAddress']) ? $item['productOrder']['shippingAddress'] : null;
            $accountId = $account->id;
            return [
                'market' => $item['order']['market'] ?? '스마트스토어',
                'marketEngName' => 'smart_store',
                'orderId' => $item['order']['orderId'] ?? 'N/A',
                'productOrderId' => $item['productOrder']['productOrderId'] ?? 'N/A',
                'orderName' => $item['order']['ordererName'] ?? 'N/A',
                'productName' => $item['productOrder']['productName'] ?? 'N/A',
                'quantity' => $item['productOrder']['quantity'] ?? 'N/A',
                'unitPrice' => $item['productOrder']['unitPrice'] ?? 'N/A',
                'totalPaymentAmount' => $item['productOrder']['totalPaymentAmount'] ?? 'N/A',
                'deliveryFeeAmount' => $item['productOrder']['deliveryFeeAmount'] ?? 'N/A',
                'productOrderStatus' => $statusMap[$item['productOrder']['productOrderStatus']] ?? '상태 미정',
                'orderDate' => isset($item['order']['orderDate']) ? (new DateTime($item['order']['orderDate']))->format('Y-m-d H:i:s') : 'N/A',
                'receiverName' => $shippingAddress ? $shippingAddress['name'] ?? 'N/A' : 'N/A',
                'receiverPhone' => $shippingAddress ? $shippingAddress['tel1'] ?? 'N/A' : 'N/A',
                'postCode' => $shippingAddress ? $shippingAddress['zipCode'] ?? 'N/A' : 'N/A',
                'address' => $shippingAddress ? ($shippingAddress['baseAddress'] . ' ' . ($shippingAddress['detailedAddress'] ?? '')) : 'N/A',
                'addressName' => '기본배송지',
                'productCode' => $item['productOrder']['sellerProductCode'] ?? 'N/A',
                'remark' => $item['productOrder']['shippingMemo'] ?? 'N/A',
                'accountId' => $accountId
            ];
        }, $response['data']['data']);
        return $formattedResponse;
    }
    private function getAccount($id)
    {
        return DB::table('smart_store_accounts')
            ->where('partner_id', $id)
            ->where('is_active', 'ACTIVE')
            ->get();
    }
}
