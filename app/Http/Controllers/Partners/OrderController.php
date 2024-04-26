<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SmartStore\SmartStoreApiController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class OrderController extends Controller
{
    private $ssac;
    private $account;
    private $contentType;

    public function __construct()
    {
        $this->ssac = new SmartStoreApiController();
        $this->account = $this->getAccount();
        $this->contentType = 'application/json';
    }
    public function index()
    {
        $orderList = $this->getOrderList($this->ssac, $this->account, $this->contentType);
        $orderIds = $this->getOrderIds($orderList);


        $responseDetail = $this->smart_storeDetail();

        $orderDetails = $this->getOrderDetails($orderIds);
        return view('partner.orders_list', [
            'orderList' => $orderList,
            'responseDetail' => $responseDetail,
            'orderDetails' => $orderDetails
        ]);
    }
    public function getOrderList($ssac, $account, $contentType)
    {
        $ssac = new SmartStoreApiController();
        $account = DB::table('smart_store_accounts')
            ->where('partner_id', Auth::guard('partner')->id())
            ->first();
        $contentType = 'application/json';
        $method = 'GET';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/last-changed-statuses';
        $data = [
            'lastChangedFrom' => Carbon::now()->subDays(1)->format('Y-m-d\TH:i:s.vP'),
            'lastChangedTo' => Carbon::now()->subDays(0)->format('Y-m-d\TH:i:s.vP'),
        ];
        $response = $ssac->builder($account, $contentType, $method, $url, $data);
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
        $ssac = new SmartStoreApiController();
        $account = DB::table('smart_store_accounts')
            ->where('partner_id', Auth::guard('partner')->id())
            ->first();
        $contentType = 'application/json';
        $method = 'GET';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/orders/2024042534417071/product-order-ids';
        $data = [];
        $response = $ssac->builder($account, $contentType, $method, $url, $data);
        return $response;
    }
    public function getOrderDetails($productOrderIds)
    {
        $ssac = new SmartStoreApiController();
        $account = DB::table('smart_store_accounts')
            ->where('partner_id', Auth::guard('partner')->id())
            ->first();
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/query';
        $data = ['productOrderIds' => $productOrderIds];
        $response = $ssac->builder($account, $contentType, $method, $url, $data);
        return $response;
    }


    protected function getAccount()
    {
        return DB::table('smart_store_accounts')->where('partner_id', Auth::guard('partner')->id())->first();
    }
}
