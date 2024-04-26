<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SmartStore\SmartStoreApiController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $response = $this->smart_store();
        $responseDetail = $this->smart_storeDetail();
        $responseOrderDetail = $this->smart_storeOrderDetail();
        return view('partner.orders_list', [
            'response' => $response,
            'responseDetail' => $responseDetail,
            'responseOrderDetail' => $responseOrderDetail
        ]);
    }
    public function smart_store()
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
    public function smart_storeOrderDetail()
    {
        $ssac = new SmartStoreApiController();
        $account = DB::table('smart_store_accounts')
            ->where('partner_id', Auth::guard('partner')->id())
            ->first();
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/query';
        $data = ['productOrderIds' => [2024042542436501]];
        $response = $ssac->builder($account, $contentType, $method, $url, $data);
        return $response;
    }
}
