<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Support\Facades\DB;

class CoupangReturnController extends Controller
{
    private $ssac;
    public function __construct()
    {
        $this->ssac = new ApiController();
    }
    public function index()
    {
        $accounts = DB::table('coupang_accounts')->where('is_active', 'ACTIVE')->get();
        $apiResults = [];
        foreach ($accounts as $account) {
            $apiResults[] = $this->getReturnList($account); //모든 신청내역 불러오기
        }
    }
    private function getReturnList($account)
    {
        $startDate = new DateTime('now - 4 days');
        $endDate = new DateTime('now');
        $contentType = 'application/json';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/returnRequests';
        $baseQuery = [
            'createdAtFrom' => $startDate->format('Y-m-d'),
            'createdAtTo' => $endDate->format('Y-m-d'),
            // 'status' => 'RECEIPT'//상태는 입력 안하는 모든 상태의 값들 넘겨짐
        ];
        $query = 'searchType=timeFrame' . http_build_query($baseQuery);
        return $this->ssac->getBuilder($account->access_key, $account->secret_key, $contentType, $path, $query);
    }
}
