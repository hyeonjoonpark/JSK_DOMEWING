<?php

namespace App\Http\Controllers\OpenMarkets\St11;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class St11OrderController extends Controller
{
    private $ssac;
    public function __construct()
    {
        $this->ssac = new ApiController();
    }
    public function index($id = null)
    {
        if ($id == null) {
            $id = Auth::guard('partner')->id();
        }
        $orderList = $this->getOrderList($id);
        return $orderList;
    }
    private function getOrderList($id)
    {
        $accounts = $this->getAccounts($id);
        if (!$accounts) {
            return false;
        }
        foreach
        $apiKey = $account->access_key;
        $method = 'post';
        $url = 'http://api.11st.co.kr/rest/prodservices/product';
    }
    private function getAccounts($id)
    {
        return DB::table('coupang_accounts')
            ->where('partner_id', $id)
            ->where('is_active', 'ACTIVE')
            ->get();
    }
}
