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
        return view('partner.orders_list', [
            'response' => $response
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
            'lastChangedFrom' => Carbon::now()->subDays(7)->format('Y-m-d\TH:i:s.vP'),
            'lastChangedTo' => Carbon::now()->addDay()->format('Y-m-d\TH:i:s.vP')
        ];
        $response = $ssac->builder($account, $contentType, $method, $url, $data);
        return $response;
    }
}
