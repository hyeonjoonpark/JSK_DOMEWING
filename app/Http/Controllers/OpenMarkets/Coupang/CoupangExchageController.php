<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CoupangExchangeController extends Controller
{
    private $ssac;
    public function __construct()
    {
        $this->ssac = new ApiController();
    }
    public function index()
    { //교환 신청 목록 가져오기
        $accounts = DB::table('coupang_accounts')->where('is_active', 'ACTIVE')->get();
        $apiResults = [];
        foreach ($accounts as $account) {
            $apiResults[] = $this->getExchangeResult($account);
        }
    }
    private function getExchangeResult($account)
    {
        $contentType = 'application/json';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/exchangeRequests';
        return $this->ssac->getBuilder($account->accessKey, $account->secretKey, $contentType, $path, $query = '');
    }
}
