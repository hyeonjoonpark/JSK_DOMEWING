<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SmartStore\SmartStoreApiController;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class OrderController extends Controller
{
    private $ssac;
    public function __construct()
    {
        $this->ssac = new SmartStoreApiController();
    }
    public function index()
    {
        $orderList = $this->getOrderList();
        $orderIds = $this->getOrderIds($orderList);


        $responseDetail = $this->smart_storeDetail();
        $orderDetails = $this->getOrderDetails($orderIds);
        return view('partner.orders_list', [
            'orderList' => $orderList,
            'responseDetail' => $responseDetail,
            'orderDetails' => $orderDetails
        ]);
    }
    public function getOrderList()
    {

        $account = $this->getAccount();
        $contentType = 'application/json';
        $method = 'GET';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/last-changed-statuses';
        $data = [
            'lastChangedFrom' => Carbon::now()->subDays(1)->format('Y-m-d\TH:i:s.vP'),
            'lastChangedTo' => Carbon::now()->subDays(0)->format('Y-m-d\TH:i:s.vP'),
        ];
        $response = $this->ssac->builder($account, $contentType, $method, $url, $data);
        return $response;
    }
    private function getOrderIds($request)
    {
        $orderIds = [];
        if (isset($request['data']['data']['lastChangeStatuses'])) {
            foreach ($request['data']['data']['lastChangeStatuses'] as $status) {
                if (isset($status['productOrderId'])) {
                    $orderIds[] = $status['productOrderId'];
                }
            }
        }
        return $orderIds;
    }
    public function smart_storeDetail()
    {

        $account = $this->getAccount();
        $contentType = 'application/json';
        $method = 'GET';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/orders/2024042534417071/product-order-ids';
        $data = [];
        $response = $this->ssac->builder($account, $contentType, $method, $url, $data);
        return $response;
    }
    public function getOrderDetails($productOrderIds)
    {
        $account = $this->getAccount();
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/query';
        $data = ['productOrderIds' => $productOrderIds];
        $response = $this->ssac->builder($account, $contentType, $method, $url, $data);

        $statusMap = [
            'PAYMENT_WAITING' => '결제 대기',
            'PAYED' => '결제 완료',
            'DELIVERING' => '배송 중',
            'DELIVERED' => '배송 완료',
            'EXCHANGED' => '교환',
            'CANCELED' => '취소',
            'RETURNED' => '반품',
            'CANCELED_BY_NOPAYMENT' => '미결제 취소',
            'PURCHASE_DECIDED' => '구매 확정'
        ];
        if (isset($response['data']) && isset($response['data']['data'])) {
            foreach ($response['data']['data'] as &$item) {
                if (isset($item['productOrder']['productOrderStatus'])) {
                    $item['productOrder']['productOrderStatus'] = $statusMap[$item['productOrder']['productOrderStatus']] ?? '상태 미정';
                }
                if (isset($item['order']['orderDate'])) {
                    $date = new DateTime($item['order']['orderDate']);
                    $item['order']['orderDate'] = $date->format('Y-m-d H:i:s');
                }
                $item['order']['market'] = '스마트스토어';
            }
        }
        return $response;
    }




    private function getAccount()
    {
        return DB::table('smart_store_accounts')->where('partner_id', Auth::guard('partner')->id())->first();
    }
}
