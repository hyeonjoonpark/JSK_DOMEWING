<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\OpenMarketExchangeRefundController;
use DateTime;
use Illuminate\Support\Facades\DB;

class CoupangExchangeController extends Controller
{
    private $ssac;
    public function __construct()
    {
        $this->ssac = new ApiController();
    }
    public function index($partnerId)
    { //교환 신청 목록 가져오기
        $accounts = $this->getActiveAccounts($partnerId); //모든 계정 가져오기
        $customerResponsibilityReasons = $this->getCustomerResponsibilityReasons();
        $results = [];
        foreach ($accounts as $account) {
            $apiResult = $this->getExchangeResult($account);
            if (!$apiResult['status'] || !isset($apiResult['data']['data'])) continue;
            foreach ($apiResult['data']['data'] as $exchangeData) { //응답이 올바르면 정보삽입
                $reasonCode = $exchangeData['reasonCode'];
                $reasonType = in_array($reasonCode, $customerResponsibilityReasons) ? '단순변심' : '상품정보와 상이';
                $results[] = $this->transformExchangeData($exchangeData, $reasonType);
            }
        }
        $openMarketExchangeRefundController = new OpenMarketExchangeRefundController();
        $createResult = [];
        foreach ($results as $result) {
            if (!$this->isExistExchangeOrder($result['newProductOrderNumber']))
                $createResult[] = $openMarketExchangeRefundController->createExchangeRefund($result);
        }
        return [
            'status' => true,
            'message' => '쿠팡교환요청수집에 성공하였습니다',
            'data' => $createResult
        ];
    }
    public function isCancelOrder($account, $productOrderNumber)
    {
        $exchangeResult = $this->getExchangeResult($account);
        if (isset($exchangeResult['status']) && $exchangeResult['status'] === true) {
            $data = $exchangeResult['data']['data'];
            foreach ($data as $exchange) {
                if (isset($exchange['exchangeItemDtoV1s']) && is_array($exchange['exchangeItemDtoV1s'])) {
                    foreach ($exchange['exchangeItemDtoV1s'] as $exchangeItem) {
                        if (isset($exchangeItem['exchangeItemId']) && $exchangeItem['exchangeItemId'] == $productOrderNumber) {
                            return [
                                'status' => false
                            ];
                        }
                    }
                }
            }
        }
        return [
            'status' => true
        ];
    }
    private function getExchangeResult($account)
    {
        $startDate = (new DateTime('now - 4 days'))->format('Y-m-d\TH:i:s');
        $endDate = (new DateTime('now'))->format('Y-m-d\TH:i:s');
        $contentType = 'application/json';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/exchangeRequests';
        $baseQuery = [
            'createdAtFrom' => $startDate,
            'createdAtTo' => $endDate,
            'status' => 'RECEIPT'
        ];
        $query = http_build_query($baseQuery);
        return $this->ssac->getBuilder($account->access_key, $account->secret_key, $contentType, $path, $query);
    }
    public function checkInExchangeRequest($account, $exchangeId) //교환 신청 확인처리
    {
        $contentType = 'application/json';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/exchangeRequests/' . $exchangeId . '/receiveConfirmation';
        $data = [
            'exchangeId' => $exchangeId,
            'vendorId' => $account->code
        ];
        return $this->ssac->putBuilder($account->access_key, $account->secret_key, $contentType, $path, $data);
    }
    public function rejectExchangeRequest($account, $exchangeId) //교환신청 거부(품절 또는 고객요청)
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
    public function exchangeShipment($account, $partnerOrder, $goodsDeliveryCode, $invoiceNumber) //교환상품 송장업로드 처리
    {
        $exchangeOrder = $this->getNewShipmentBoxId($account, $partnerOrder->order_number);
        if (
            isset($exchangeOrder['data']['data'][0]) &&
            isset($exchangeOrder['data']['data'][0]['deliveryInvoiceGroupDtos']) &&
            is_array($exchangeOrder['data']['data'][0]['deliveryInvoiceGroupDtos']) &&
            !empty($exchangeOrder['data']['data'][0]['deliveryInvoiceGroupDtos']) &&
            isset($exchangeOrder['data']['data'][0]['exchangeId'])
        ) {
            $exchangeData = $exchangeOrder['data']['data'][0];
            $deliveryInvoiceGroupDtos = $exchangeData['deliveryInvoiceGroupDtos'];
            $exchangeId = $exchangeData['exchangeId'];
            $shipmentBoxId = $deliveryInvoiceGroupDtos[0]['shipmentBoxId'];
        } else {
            return [
                'status' => false,
                'message' => '교환건 조회에 실패하였습니다. 관리자에게 문의해주세요.'
            ];
        }
        $method = 'POST';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/exchangeRequests/' . $exchangeId . '/invoices';
        $contentType = 'application/json;charset=UTF-8';
        $data = [
            [
                'exchangeId' => $exchangeId,
                'vendorId' => $account->code,
                'shipmentBoxId' => $shipmentBoxId,
                'goodsDeliveryCode' => $goodsDeliveryCode,
                'invoiceNumber' => $invoiceNumber,
            ]
        ];
        return $this->ssac->builder($account->access_key, $account->secret_key, $method, $contentType, $path, $data);
    }
    private function isExistExchangeOrder($newProductOrderNumber)
    {
        return DB::table('partner_orders')
            ->where('product_order_number', $newProductOrderNumber)
            ->first();
    }
    private function getActiveAccounts($partnerId)
    {
        return DB::table('coupang_accounts')
            ->where('is_active', 'ACTIVE')
            ->where('partner_id', $partnerId)
            ->get();
    }
    private function getCustomerResponsibilityReasons()
    {
        return [
            'CHANGEMIND',
            'DIFFERENTOPT',
            'DONTLIKESIZECOLOR',
            'CHEAPER',
            'WRONGOPT',
            'WRONGADDRESS',
            'REORDER',
            'OTHERS',
            'INACCURATE',
            'SYSTEMERROR',
            'SYSTEMINFO_ERROR',
            'EXITERROR',
            'PAYERROR',
            'PRICEERROR',
            'PARTNERERROR',
            'REGISTERROR',
            'NOTPURCHASABLE'
        ];
    }
    private function transformExchangeData($exchangeData, $reasonType)
    {
        return [
            'requestType' => 'EXCHANGE',
            'reasonType' => $reasonType,
            'reason' => $exchangeData['reasonEtcDetail'],
            'productOrderNumber' => $exchangeData['exchangeItemDtoV1s'][0]['originalShipmentBoxId'],
            'quantity' => $exchangeData['exchangeItemDtoV1s'][0]['quantity'],
            'newProductOrderNumber' => $exchangeData['exchangeItemDtoV1s'][0]['exchangeItemId'],
            'receiverName' => $exchangeData['exchangeAddressDtoV1']['returnCustomerName'],
            'receiverPhone' => $exchangeData['exchangeAddressDtoV1']['returnMobile'] ?? $exchangeData['exchangeAddressDtoV1']['returnPhone'] ?? '01000000000',
            'receiverAddress' => $exchangeData['exchangeAddressDtoV1']['deliveryAddress'] . ' ' . $exchangeData['exchangeAddressDtoV1']['deliveryAddressDetail'],
            'createdAt' => $exchangeData['createdAt']
        ];
    }
    private function getNewShipmentBoxId($account, $orderID)
    {
        $startDate = (new DateTime('now - 4 days'))->format('Y-m-d\TH:i:s');
        $endDate = (new DateTime('now'))->format('Y-m-d\TH:i:s');
        $contentType = 'application/json';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/exchangeRequests';
        $baseQuery = [
            'createdAtFrom' => $startDate,
            'createdAtTo' => $endDate,
            'orderId' => $orderID
        ];
        $query = http_build_query($baseQuery);
        return $this->ssac->getBuilder($account->access_key, $account->secret_key, $contentType, $path, $query);
    }
}
