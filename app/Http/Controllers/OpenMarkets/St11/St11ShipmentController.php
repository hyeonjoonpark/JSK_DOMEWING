<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\St11\ApiController;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class St11ShipmentController extends Controller
{
    private $ssac;
    public function __construct()
    {
        $this->ssac = new ApiController();
    }
    public function index(Request $request)
    {
        try { //데이터 유효값 검증을 또 하지 않음 왜냐하면 openMarketShipmentController에서 이미 검증을 한 이후에 데이터를 넘겨주기 때문.
            $trackingNumber = $request->trackingNumber;
            $deliveryCompanyId = $request->deliveryCompanyId;
            $productOrderNumber = $request->productOrderNumber;
            $deliveryCompany = $this->getDeliveryCompany($deliveryCompanyId);
            $order = $this->getOrder($productOrderNumber);
            $partnerOrder = $this->getPartnerOrder($order->id);
            $account = $this->getAccount($partnerOrder->account_id);
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '데이터 조회 중 오류가 발생했습니다: ' . $e->getMessage(),
            ];
        }
        try {
            $responseApi = $this->postApi($account, $deliveryCompany->coupang, $trackingNumber);
            if (!$responseApi['status']) {
                return [
                    'status' => false,
                    'message' => '쿠팡 송장번호 입력 중 오류가 발생하였습니다.',
                    'data' => $responseApi,
                ];
            }
            return [
                'status' => true,
                'message' => '송장번호 입력에 성공하였습니다',
                'data' => $responseApi
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    private function getSingleOrder($account, $productOrderNumber) //발주서 단건 조회
    {
        $contentType = 'application/json;charset=UTF-8';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/ordersheets' . '/' .  $productOrderNumber;
        return $this->ssac->getBuilder($account->access_key, $account->secret_key, $contentType, $path);
    }

    private function postApi($account, $deliveryCompanyCode, $invoiceNumber)
    {
        $method = 'GET';
        $nowDate = new DateTime('now');
        $url = 'https://api.11st.co.kr/rest/ordservices/reqdelivery/' . $nowDate . '/01/' . $deliveryCompanyCode . '/' . $invoiceNumber . '/[dlvNo]'; //배송번호?
        return $this->ssac->builder($account->access_key, $method, $url);
    }

    private function getDeliveryCompany($deliveryCompanyId)
    {
        return DB::table('delivery_companies as dc')
            ->where('dc.id', $deliveryCompanyId)
            ->first();
    }

    private function getOrder($productOrderNumber)
    {
        return DB::table('orders')
            ->where('product_order_number', $productOrderNumber)
            ->first();
    }

    private function getPartnerByWingTransactionId($wingTransactionId)
    {
        return DB::table('wing_transactions as wt')
            ->join('partner_domewing_accounts as pda', 'wt.member_id', '=', 'pda.domewing_account_id')
            ->join('partners as p', 'pda.partner_id', '=', 'p.id')
            ->where('wt.id', $wingTransactionId)
            ->where('pda.is_active', 'Y')
            ->select('p.*')
            ->first();
    }
    private function getPartnerOrder($orderId)
    {
        return DB::table('partner_orders as ps')
            ->where('ps.order_id', $orderId)
            ->first();
    }

    private function getAccount($accountId)
    {
        return DB::table('coupang_accounts')
            ->where('id', $accountId)
            ->where('is_active', 'ACTIVE')
            ->first();
    }
    private function getDispatchDate()
    {
        $dateTime = new DateTime('now', new DateTimeZone('Asia/Seoul'));
        $dateTime->modify('+3 days'); // 현재 날짜로부터 3일 뒤로 설정
        return $dateTime->format('YmdHi');
    }
}
