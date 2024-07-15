<?php

namespace App\Http\Controllers\OpenMarkets\St11;

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
        try {
            // 데이터 유효성 검증은 이미 수행되었으므로 생략
            $trackingNumber = $request->trackingNumber;
            $deliveryCompanyId = $request->deliveryCompanyId;
            $productOrderNumber = $request->productOrderNumber;
            $deliveryCompany = $this->getDeliveryCompany($deliveryCompanyId);
            $order = $this->getOrder($productOrderNumber);
            $partnerOrder = $this->getPartnerOrder($order->id);
            $account = $this->getAccount($partnerOrder->account_id);
            list($ordPrdSeq, $dlvNo) = explode('/', $partnerOrder->product_order_number);
            // 취소 요청 체크 및 강제출고
            $cancelCheckResult = $this->checkCancelOrder($order, $account);
            if ($cancelCheckResult['status']) {
                $shipResponse = $this->forceShipOrder($account, $deliveryCompany->st11, $trackingNumber, $partnerOrder, $ordPrdSeq, $cancelCheckResult['data']);
                if (!$shipResponse['status']) return [
                    'status' => false,
                    'message' => '강제출고처리에 실패하였습니다. 관리자에게 문의바랍니다.',
                    'data' => $shipResponse
                ];
                return [
                    'status' => true,
                    'message' => '11번가 주문취소 요청이 있어 강제출고 처리되었습니다.',
                    'cancelled' => true
                ];
            }
            // 배송 요청 처리
            $responseApi = $this->postApi($account, $deliveryCompany->st11, $trackingNumber, $dlvNo);
            if (!$responseApi['status']) {
                return [
                    'status' => false,
                    'message' => '11번가 송장번호 입력 중 오류가 발생하였습니다.',
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
                'message' => '처리 중 오류가 발생했습니다: ' . $e->getMessage(),
            ];
        }
    }
    private function checkCancelOrder($order, $account)
    {
        $partnerOrder = DB::table('partner_orders')
            ->where('order_id', $order->id)
            ->first();
        list($ordPrdSeq, $dlvNo) = explode('/', $partnerOrder->product_order_number);
        $apiKey = $account->access_key;
        $controller = new ApiController();
        $method = 'GET';
        $startDate = (new DateTime('now - 4 days'))->format('YmdHi');
        $endDate = (new DateTime('now'))->format('YmdHi');
        $url = 'http://api.11st.co.kr/rest/claimservice/cancelorders/' . $startDate . '/' . $endDate;
        $data = $controller->orderBuilder($apiKey, $method, $url); // 취소신청목록조회
        if (isset($data['data']['ns2:order'])) {
            if (is_array($data['data']['ns2:order']) && isset($data['data']['ns2:order'][0])) {
                // 주문이 여러 개일 때
                foreach ($data['data']['ns2:order'] as $stOrder) {
                    if (strpos($stOrder['dlvNo'], $dlvNo) !== false && $stOrder['ordCnStatCd'] === '01') {
                        return [
                            'status' => true,
                            'message' => '11번가 취소요청 주문입니다.',
                            'data' => $stOrder['ordPrdCnSeq']
                        ];
                    }
                }
            } else {
                // 주문이 하나일 때
                $stOrder = $data['data']['ns2:order'];
                if (strpos($stOrder['dlvNo'], $dlvNo) !== false && $stOrder['ordCnStatCd'] === '01') {
                    return [
                        'status' => true,
                        'message' => '11번가 취소요청 주문입니다.',
                        'data' => $stOrder['ordPrdCnSeq']
                    ];
                }
            }
        }
        return [
            'status' => false,
            'message' => '11번가 취소 요청건이 아닙니다.',
        ];
    }
    private function forceShipOrder($account, $deliveryCompanyCode, $invoiceNumber, $partnerOrder, $ordPrdSeq, $ordPrdCnSeq)
    {
        $method = 'GET';
        $nowDate = new DateTime('now');
        $formattedDate = $nowDate->format('Ymd');
        $url = 'http://api.11st.co.kr/rest/claimservice/cancelreqreject/' . $partnerOrder->order_number . '/' . $ordPrdSeq . '/' . $ordPrdCnSeq . '/01/' . $formattedDate . '/' . $deliveryCompanyCode . '/' . $invoiceNumber;
        return $this->ssac->builder($account->access_key, $method, $url);
    }

    private function postApi($account, $deliveryCompanyCode, $invoiceNumber, $dlvNo)
    {
        $method = 'GET';
        $nowDate = new DateTime('now');
        $formattedDate = $nowDate->format('YmdHi');
        $url = 'https://api.11st.co.kr/rest/ordservices/reqdelivery/' . $formattedDate . '/01/' . $deliveryCompanyCode . '/' . $invoiceNumber . '/' . $dlvNo;
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
    private function getPartnerOrder($orderId)
    {
        return DB::table('partner_orders as ps')
            ->where('ps.order_id', $orderId)
            ->first();
    }

    private function getAccount($accountId)
    {
        return DB::table('st11_accounts')
            ->where('id', $accountId)
            ->where('is_active', 'ACTIVE')
            ->first();
    }
}
