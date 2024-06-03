<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use DateTime;
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
            $apiResults[] = $this->getExchangeResult($account); //모든 신청내역 불러오기
        }
        foreach ($apiResults as $apiResult) {
            $exchangeId = $apiResult['data']['exchangeAddressDtoV1']['exchangeId'];
        }
    }
    private function getExchangeResult($account) //교환신청 내역 불러오기
    {
        $startDate = new DateTime('now - 4 days');
        $endDate = new DateTime('now');
        $contentType = 'application/json';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/exchangeRequests';
        $baseQuery = [
            'createdAtFrom' => $startDate->format('Y-m-d'),
            'createdAtTo' => $endDate->format('Y-m-d'),
            // 'status' => 'RECEIPT'//상태는 입력 안하는 모든 상태의 값들 넘겨짐
        ];
        $query = http_build_query($baseQuery);
        return $this->ssac->getBuilder($account->access_key, $account->secret_key, $contentType, $path, $query);
    }
    private function checkInExchangeRequest($account, $exchangeId) //교환 신청 확인처리
    {
        $contentType = 'application/json';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/exchangeRequests/' . $exchangeId . '/receiveConfirmation';
        $data = [
            'exchangeId' => $exchangeId,
            'vendorId' => $account->code
        ];
        return $this->ssac->putBuilder($account->access_key, $account->secret_key, $contentType, $path, $data);
    }
    private function rejectExchangeRequest($account, $exchangeId) //교환신청 거부(품절 또는 고객요청)
    {
        $contentType = 'application/json';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/exchangeRequests/' . $exchangeId . '/rejection';
        $data = [
            'exchangeId' => $exchangeId,
            'vendorId' => $account->code,
            'exchangeRejectCode' => 'SOLDOUT' //WITHDRAW (고객이 요청했을때는 withdraw)
        ];
        return $this->ssac->putBuilder($account->access_key, $account->secret_key, $contentType, $path, $data);
    }
    private function exchangeShipment($account, $exchangeId, $shipmentBoxId, $goodsDeliveryCode, $invoiceNumber) //교환상품 송장업로드 처리
    {
        $method = 'POST';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/exchangeRequests/' . $exchangeId . '/invoices';
        $contentType = 'application/json;charset=UTF-8';
        $data = [
            'vendorId' => $account->code,
            'orderSheetInvoiceApplyDtos' => [
                [
                    'exchangeId' => $exchangeId,
                    'vendorId' => $account->code,
                    'shipmentBoxId' => $shipmentBoxId,
                    'goodsDeliveryCode' => $goodsDeliveryCode, //택배사
                    'invoiceNumber' => $invoiceNumber, //송장번호
                ]
            ]
        ];
        return $this->ssac->builder($account->access_key, $account->secret_key, $method, $contentType, $path, $data);
    }
}
