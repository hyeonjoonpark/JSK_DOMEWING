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
            $returnOrderList = $this->getReturnOrderList($account);
            $orderIds = $this->getOrderIds($returnOrderList);
            $orderDetails = $this->getOrderDetails($account, $orderIds);
            if (isset($orderDetails['error'])) {
                continue; // 오류가 있으면 다음 계정으로 넘어감
            }
            $allOrderDetails[] = $returnOrderList;
        }
        return  $allOrderDetails;
    }
    private function getReturnOrderList($account)
    {
        $contentType = 'application/json';
        $method = 'GET';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/last-changed-statuses';
        $startDate = new DateTime('now - 4 days');
        $endDate = new DateTime('now');
        $returnOrders = [];
        for ($date = $startDate; $date <= $endDate; $date->modify('+1 day')) {
            $formattedDate = $this->convertDateFormat($date->format('Y-m-d'));
            $data = ['lastChangedFrom' => $formattedDate];
            $response = $this->ssac->builder($account, $contentType, $method, $url, $data);
            if ($response['status'] && isset($response['data']['data']['lastChangeStatuses'])) {
                foreach ($response['data']['data']['lastChangeStatuses'] as $status) {
                    if ($status['productOrderStatus'] === 'RETURNED') {
                        $returnOrders[] = $status;
                    }
                }
            }
        }
        return $returnOrders;
    }
    private function convertDateFormat($inputDate)
    {
        $date = new DateTime($inputDate, new DateTimeZone('Asia/Seoul'));
        return $date->format('Y-m-d\TH:i:s.vP');
    }
    private function getOrderIds($returnOrderList)
    {
        $orderIds = [];
        foreach ($returnOrderList as $returnOrder) {
            $orderIds[] = $returnOrder['productOrderId'];
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
        foreach ($response['data']['data'] as $item) {
            // if (isset($item['return']) && $item['return']['claimStatus'] == 'RETURN_REQUEST') {
            if (isset($item['return'])) {
                return $item['return'];
            }
        }
        return [];
    }
    private function getAccounts()
    {
        return DB::table('smart_store_accounts')
            // ->where('partner_id', $id)
            ->where('partner_id', 3)
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
