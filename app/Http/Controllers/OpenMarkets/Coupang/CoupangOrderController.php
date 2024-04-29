<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\ApiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CoupangOrderController extends Controller
{
    private $ssac;
    public function __construct()
    {
        $this->ssac = new ApiController();
    }
    public function index()
    {
        $orderList = $this->getOrderList();
        return view('partner.testOrderList', [
            'orderList' => $orderList
        ]);
    }
    public function getOrderList()
    {
        $account = $this->getAccount();
        $accessKey = $account->access_key;
        $secretKey = $account->secret_key;
        $vendorId = $account->code;

        $contentType = 'application/json';
        $method = 'GET';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $vendorId . '/ordersheets';
        $data = [
            'createdAtFrom' => "2024-04-24",
            'createdAtTo' => "2024-04-29",
            'status' => "ACCEPT"
        ];
        $response = $this->ssac->builder($accessKey, $secretKey, $method, $contentType, $path, $data);

        return $response;
    }




    private function getAccount()
    {
        return DB::table('coupang_accounts')->where('partner_id', Auth::guard('partner')->id())->first();
    }
}
