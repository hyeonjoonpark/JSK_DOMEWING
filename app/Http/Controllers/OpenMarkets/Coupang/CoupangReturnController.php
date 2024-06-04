<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Support\Facades\DB;

class CoupangReturnController extends Controller
{
    //쿠팡 반품 flow 링크
    //https://developers.coupangcorp.com/hc/ko/articles/360033643294-%EB%B0%98%ED%92%88-API-%EC%9B%8C%ED%81%AC%ED%94%8C%EB%A1%9C%EC%9A%B0
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
            $apiResult = $this->getReturnList($account); // 모든 신청 내역 불러오기
            if (!$apiResult['status']) continue;
            $receiptIds = $this->extractReceiptIds($apiResult['data']['data']);
            foreach ($receiptIds as $receiptId) {
                $singleReturnResult = $this->getSingleReturnRequest($account, $receiptId);
                $apiResults[] = $singleReturnResult;
            }
        }
        return $apiResults;
    }
    private function getReturnList($account)  //반품내역 불러오기 - 테스트 완료
    {
        $startDate = new DateTime('now - 4 days');
        $endDate = new DateTime('now');
        $contentType = 'application/json';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/returnRequests';
        $baseQuery = [
            'createdAtFrom' => $startDate->format('Y-m-d\TH:i'),
            'createdAtTo' => $endDate->format('Y-m-d\TH:i'),
            'status' => 'UC'
        ];
        $query = 'searchType=timeFrame&' . http_build_query($baseQuery);
        return $this->ssac->getBuilder($account->access_key, $account->secret_key, $contentType, $path, $query);
    }
    private function getSingleReturnRequest($account, $receiptId) //반품요청 단건 조회 - 테스트 완료
    {
        $contentType = 'application/json';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/returnRequests/' . $receiptId;
        $response =  $this->ssac->getBuilder($account->access_key, $account->secret_key, $contentType, $path);
        $transformResult = $this->transformReturnDetails($response, $account);
    }
    private function transformReturnDetails($response, $account)
    {
        if (!isset($response['data']['data'])) {
            return false;
        }
        $orderDetails = [];
        if (!empty($response['data']['data'])) {
            foreach ($response['data']['data'] as $item) {
                $orderDetails[] = [
                    'market' => '쿠팡',
                    'marketEngName' => 'coupang',
                    'orderId' => strval($item['orderId']),
                    'productOrderId' => strval($item['shipmentBoxId']),
                    'orderName' => $item['orderer']['name'],
                    'productName' => $orderItem['vendorItemName'],
                    'quantity' => $orderItem['shippingCount'],
                    'unitPrice' => $orderItem['salesPrice'],
                    'totalPaymentAmount' => $orderItem['orderPrice'],
                    'deliveryFeeAmount' => $item['shippingPrice'],
                    'productOrderStatus' => $this->mapStatusToReadable($item['status']),
                    'orderDate' => isset($item['paidAt']) ? (new DateTime($item['paidAt']))->format('Y-m-d H:i:s') : 'N/A',
                    'receiverName' => $item['requesterName'],
                    'receiverPhone' => $item['requesterPhoneNumber'],
                    'postCode' => $item['requesterZipCode'],
                    'address' => $item['receiver']['addr1'] . ' ' . $item['receiver']['addr2'],
                    'addressName' => '기본배송지',
                    'productCode' => $orderItem['externalVendorSkuCode'] ?? 'N/A',
                    'remark' => $item['cancelReason'] ?? 'N/A',
                    'cancelCountSum' => $item['cancelCountSum'],
                    'returnDeliveryId' => $item['returnDeliveryId'],
                    'accountId' => $account->id
                ];
            }
        }
        return $orderDetails;
    }
    private function extractReceiptIds($data)
    {
        $receiptIds = [];
        if (is_array($data)) {
            foreach ($data as $item) {
                if (isset($item['receiptId'])) {
                    $receiptIds[] = $item['receiptId'];
                }
            }
        }
        return $receiptIds;
    }
    private function confirmReturnReceipt($account, $receiptId) //반품상품 입고 확인처리
    {
        $accessKey = $account->access_key;
        $secretKey = $account->secret_key;
        $contentType = 'application/json;charset=UTF-8';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/returnRequests/' . $receiptId . '/receiveConfirmation';
        $data = [
            'vendorId' => $account->code,
            'receiptId' => $receiptId
        ];
        return $this->ssac->putBuilder($accessKey, $secretKey, $contentType, $path, $data);
    }
    private function approveReturnRequest($account, $receiptId, $cancelCount) //반품요청 승인 처리
    {
        $accessKey = $account->access_key;
        $secretKey = $account->secret_key;
        $contentType = 'application/json;charset=UTF-8';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/returnRequests/' . $receiptId . '/approval';
        $data = [
            'vendorId' => $account->code,
            'receiptId' => $receiptId,
            'cancelCount' => $cancelCount //반품접수수량
        ];
        return $this->ssac->putBuilder($accessKey, $secretKey, $contentType, $path, $data);
    }
    public function registerReturnInvoice($account, $returnExchangeDeliveryType, $receiptId, $deliveryCompanyCode, $invoiceNumber) //회수 송장 등록
    {
        $method = 'POST';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/return-exchange-invoices/manual';
        $contentType = 'application/json;charset=UTF-8';
        $data = [
            'returnExchangeDeliveryType' => $returnExchangeDeliveryType,
            'receiptId' => $receiptId,
            'deliveryCompanyCode' => $deliveryCompanyCode,
            'invoiceNumber' => $invoiceNumber
        ];
        return $this->ssac->builder($account->access_key, $account->secret_key, $method, $contentType, $path, $data);
    }
}
