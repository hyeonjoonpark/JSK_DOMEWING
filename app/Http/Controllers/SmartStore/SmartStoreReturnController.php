<?php

namespace App\Http\Controllers\SmartStore;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SmartStore\SmartStoreApiController;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SmartStoreReturnController extends Controller
{
    private $ssac;
    public function __construct()
    {
        $this->ssac = new SmartStoreApiController();
    }
    public function index() //$id값주기
    {
        $accounts = $this->getAccounts(); //$id값주기
        if (!$accounts) {
            return false;
        }
        $allOrderDetails = [];
        foreach ($accounts as $account) {
            $orderList = $this->getOrderList($account);
            $orderIds = $this->getOrderIds($orderList);
            $orderDetails = $this->getOrderDetails($account, $orderIds);
            if (isset($orderDetails['error'])) {
                continue; // 오류가 있으면 다음 계정으로 넘어감
            }
            $allOrderDetails[] = $orderDetails;
        }
        return  $allOrderDetails;
    }
    private function getOrderList($account)
    {
        $contentType = 'application/json';
        $method = 'GET';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/last-changed-statuses';
        $startDate = new DateTime('now - 7 days');
        $endDate = new DateTime('now');
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

        if (isset($response['data']['data']['return'])) return $response['data']['data']['return'];
        return [];

        // $statusMap = [
        //     'CANCEL_REQUEST' => '취소 요청',
        //     'CANCELING' => '취소 처리 중',
        //     'CANCEL_DONE' => '취소 처리 완료',
        //     'CANCEL_REJECT' => '취소 철회',
        //     'RETURN_REQUEST' => '반품 요청',
        //     'EXCHANGE_REQUEST' => '교환 요청',
        //     'COLLECTING' => '수거 처리 중',
        //     'COLLECT_DONE' => '수거 완료',
        //     'EXCHANGE_REDELIVERING' => '교환 재배송 중',
        //     'RETURN_DONE' => '반품 완료',
        //     'EXCHANGE_DONE' => '교환 완료',
        //     'RETURN_REJECT' => '반품 철회',
        //     'EXCHANGE_REJECT' => '교환 철회',
        //     'PURCHASE_DECISION_HOLDBACK' => '구매 확정 보류',
        //     'PURCHASE_DECISION_REQUEST' => '구매 확정 요청',
        //     'PURCHASE_DECISION_HOLDBACK_RELEASE' => '구매 확정 보류 해제',
        //     'ADMIN_CANCELING' => '직권 취소 중',
        //     'ADMIN_CANCEL_DONE' => '직권 취소 완료',
        //     'ADMIN_CANCEL_REJECT' => '직권 취소 철회',
        // ];
        // $formattedResponse = array_map(function ($item) use ($statusMap, $account) {
        //     $return = $item['return'] ?? null;
        //     $accountId = $account->id;
        //     return [
        //         'market' => $item['order']['market'] ?? '스마트스토어',
        //         'marketEngName' => 'smart_store',
        //         'orderId' => $item['order']['orderId'] ?? 'N/A',
        //         'productOrderId' => $item['productOrder']['productOrderId'] ?? 'N/A',
        //         'orderName' => $item['order']['ordererName'] ?? 'N/A',
        //         'productName' => $item['productOrder']['productName'] ?? 'N/A',
        //         'quantity' => $item['productOrder']['quantity'] ?? 'N/A',
        //         'unitPrice' => $item['productOrder']['unitPrice'] ?? 'N/A',
        //         'totalPaymentAmount' => $item['productOrder']['totalPaymentAmount'] ?? 'N/A',
        //         'deliveryFeeAmount' => $item['productOrder']['deliveryFeeAmount'] ?? 'N/A',
        //         'productOrderStatus' => $statusMap[$item['return']['claimStatus']] ?? '상태 미정',
        //         'orderDate' => isset($item['order']['orderDate']) ? (new DateTime($item['order']['orderDate']))->format('Y-m-d H:i:s') : 'N/A',
        //         'receiverName' => $item['collectAddress']['name'] ?? 'N/A',
        //         'receiverPhone' => $item['collectAddress']['tel1'] ?? 'N/A',
        //         'postCode' => $item['collectAddress']['zipCode'] ?? 'N/A',
        //         'address' => ($item['collectAddress']['baseAddress'] . ' ' . ($item['collectAddress']['detailedAddress'] ?? '')) ?? 'N/A',
        //         'addressName' => '기본배송지',
        //         'productCode' => $item['productOrder']['sellerProductCode'] ?? 'N/A',
        //         'remark' => $item['productOrder']['shippingMemo'] ?? 'N/A',
        //         'accountId' => $accountId

        //     ];
        // }, $response['data']['data']);
        return $formattedResponse;
    }
    private function getAccounts() //id값주기
    {
        return DB::table('smart_store_accounts')
            // ->where('partner_id', $id)
            ->where('is_active', 'ACTIVE')
            ->get();
    }
    private function approveReturnRequest($account, $productOrderId) //반품승인
    {
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/return/approve';
        $data = [];

        return $this->ssac->builder($account, $contentType, $method, $url, $data);
    }
    private function rejectReturnRequest($account, $productOrderId, $rejectReturnReason) //반품거부
    {
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/return/reject';
        $data = [
            'rejectReturnReason' => $rejectReturnReason
        ];

        return $this->ssac->builder($account, $contentType, $method, $url, $data);
    }
}
