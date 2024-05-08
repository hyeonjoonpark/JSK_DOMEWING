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
        $account = $this->getAccount($id);
        $orderList = $this->getOrderList($account, $start, $end);
        $orderIds = $this->getOrderIds($orderList);
        $orderDetails = $this->getOrderDetails($account, $orderIds);
        return  $orderDetails;
    }
    private function getOrderList($account, $start = null, $end = null)
    {
        $contentType = 'application/json';
        $method = 'GET';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/last-changed-statuses';
        $startDate = $start ? new DateTime($start) : new DateTime('now - 6 days');
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
            'PAYMENT_WAITING' => '결제 대기',
            'PAYED' => '결제 완료',
            'DELIVERING' => '배송 중',
            'DELIVERED' => '배송 완료',
            'EXCHANGED' => '교환',
            'CANCELED' => '취소',
            'RETURNED' => '반품',
            'CANCELED_BY_NOPAYMENT' => '미결제 취소',
            'PURCHASE_DECIDED' => '구매 확정',
        ];
        $formattedResponse = array_map(function ($item) use ($statusMap) {
            return [
                'market' => $item['order']['market'] ?? '스마트스토어',
                'orderId' => $item['order']['orderId'] ?? 'N/A',
                'orderName' => $item['order']['ordererName'] ?? 'N/A',
                'orderDate' => isset($item['order']['orderDate']) ? (new DateTime($item['order']['orderDate']))->format('Y-m-d H:i:s') : 'N/A',
                'productOrderId' => $item['productOrder']['productOrderId'] ?? 'N/A',
                'productName' => $item['productOrder']['productName'] ?? 'N/A',
                'quantity' => $item['productOrder']['quantity'] ?? 'N/A',
                'unitPrice' => $item['productOrder']['unitPrice'] ?? 'N/A',
                'totalPaymentAmount' => $item['productOrder']['totalPaymentAmount'] ?? 'N/A',
                'deliveryFeeAmount' => $item['productOrder']['deliveryFeeAmount'] ?? 'N/A',
                'productOrderStatus' => $statusMap[$item['productOrder']['productOrderStatus']] ?? '상태 미정',
            ];
        }, $response['data']['data']);
        return $formattedResponse;
    }
    private function getAccount($id)
    {
        return DB::table('smart_store_accounts')->where('partner_id', $id)->first();
    }
}
