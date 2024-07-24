<?php

namespace App\Http\Controllers\SmartStore;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\OpenMarketExchangeRefundController;
use App\Http\Controllers\SmartStore\SmartStoreApiController;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\DB;

class SmartStoreExchangeController extends Controller
{
    private $ssac;
    public function __construct()
    {
        $this->ssac = new SmartStoreApiController();
    }
    public function index() //$id값주기
    {
        $accounts = $this->getAccounts(); //$id값주기
        if (!$accounts) {
            return false;
        }
        $allOrderDetails = [];
        foreach ($accounts as $account) {
            $returnOrderList = $this->getExchangeOrderList($account);
            $orderIds = $this->getOrderIds($returnOrderList);
            $exchangeDatas = $this->getOrderDetails($account, $orderIds);
            if (!$exchangeDatas) continue;
            $results =  $this->transformExchangeData($exchangeDatas);
            foreach ($results as $result) {
                if (!$this->isExistReturnOrder($result['newProductOrderNumber'])) {
                    $openMarketExchangeRefundController = new OpenMarketExchangeRefundController();
                    $createResult[] = $openMarketExchangeRefundController->createExchangeRefund($result);
                }
            }
        }
        return ['status' => true, 'message' => '스마트스토어 환불 요청 수집에 성공하였습니다', 'data' => $createResult];
    }
    private function getExchangeOrderList($account)
    {
        $contentType = 'application/json';
        $method = 'GET';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/last-changed-statuses';
        $startDate = new DateTime('now - 7 days');
        $endDate = new DateTime('now');
        $returnOrders = [];
        for ($date = $startDate; $date <= $endDate; $date->modify('+1 day')) {
            $formattedDate = $this->convertDateFormat($date->format('Y-m-d'));
            $data = ['lastChangedFrom' => $formattedDate];
            $response = $this->ssac->builder($account, $contentType, $method, $url, $data);
            if ($response['status'] && isset($response['data']['data']['lastChangeStatuses'])) {
                foreach ($response['data']['data']['lastChangeStatuses'] as $status) {
                    if (isset($status['claimStatus']) && $status['claimStatus'] === 'EXCHANGE_REQUEST') {
                        $returnOrders[] = $status;
                    }
                }
            }
        }
        return $returnOrders;
    }
    private function convertDateFormat($inputDate)
    {
        $date = new DateTime($inputDate, new DateTimeZone('Asia/Seoul'));
        return $date->format('Y-m-d\TH:i:s.vP');
    }
    private function getOrderIds($returnOrderList)
    {
        $orderIds = [];
        foreach ($returnOrderList as $returnOrder) {
            $orderIds[] = $returnOrder['productOrderId'];
        }
        return $orderIds;
    }
    private function getOrderDetails($account, $productOrderIds)
    {
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/query';
        $data = ['productOrderIds' => $productOrderIds];
        $response = $this->ssac->builder($account, $contentType, $method, $url, $data);
        if (!isset($response['data']['data'])) {
            return false;
        }
        foreach ($response['data']['data'] as $item) {
            if (isset($item['return']) && $item['return']['claimStatus'] == 'EXCHANGE_REQUEST' && isset($item['return']['returnDetailedReason'])) {
                return $response;
            }
        }
        return false;
    }
    private function transformExchangeData($exchangeDatas)
    {
        $result = [];
        $exchangeDatas = $exchangeDatas['data']['data'];
        $simpleChangeReasons = [
            'PRODUCT_UNSATISFIED',
            'DELAYED_DELIVERY',
            'SOLD_OUT',
            'DROPPED_DELIVERY',
            'BROKEN',
            'INCORRECT_INFO',
            'WRONG_DELIVERY',
            'WRONG_OPTION',
            'WRONG_DELAYED_DELIVERY',
            'BROKEN_AND_BAD',
            'UNDER_QUANTITY',
            'ETC'
        ];
        foreach ($exchangeDatas as $data) {
            $returnReason = $data['return']['returnReason'];
            $reasonType = in_array($returnReason, $simpleChangeReasons) ? '상품정보와 상이' : '단순변심';
            $receiverPhone = $data['productOrder']['shippingAddress']['tel1']
                ?? $data['productOrder']['shippingAddress']['tel2']
                ?? '01000000000';
            $reasonMapping = [
                'INTENT_CHANGED' => '구매 의사 취소',
                'COLOR_AND_SIZE' => '색상 및 사이즈 변경',
                'WRONG_ORDER' => '다른 상품 잘못 주문',
                'PRODUCT_UNSATISFIED' => '서비스 불만족',
                'DELAYED_DELIVERY' => '배송 지연',
                'SOLD_OUT' => '상품 품절',
                'DROPPED_DELIVERY' => '배송 누락',
                'NOT_YET_DELIVERY' => '미배송',
                'BROKEN' => '상품 파손',
                'INCORRECT_INFO' => '상품 정보 상이',
                'WRONG_DELIVERY' => '오배송',
                'WRONG_OPTION' => '색상 등 다른 상품 잘못 배송',
                'SIMPLE_INTENT_CHANGED' => '단순 변심',
                'MISTAKE_ORDER' => '주문 실수',
                'ETC' => '기타',
                'DELAYED_DELIVERY_BY_PURCHASER' => '배송 지연',
                'INCORRECT_INFO_BY_PURCHASER' => '상품 정보 상이',
                'PRODUCT_UNSATISFIED_BY_PURCHASER' => '서비스 불만족',
                'NOT_YET_DISCUSSION' => '상호 협의가 완료되지 않은 주문 건',
                'OUT_OF_STOCK' => '재고 부족으로 인한 판매 불가',
                'SALE_INTENT_CHANGED' => '판매 의사 변심으로 인한 거부',
                'NOT_YET_PAYMENT' => '구매자의 미결제로 인한 거부',
                'NOT_YET_RECEIVE' => '상품 미수취',
                'WRONG_DELAYED_DELIVERY' => '오배송 및 지연',
                'BROKEN_AND_BAD' => '파손 및 불량',
                'RECEIVING_DUE_DATE_OVER' => '수락 기한 만료',
                'RECEIVER_MISMATCHED' => '수신인 불일치',
                'GIFT_INTENT_CHANGED' => '보내기 취소',
                'GIFT_REFUSAL' => '선물 거절',
                'MINOR_RESTRICTED' => '상품 수신 불가',
                'RECEIVING_BLOCKED' => '상품 수신 불가',
                'UNDER_QUANTITY' => '주문 수량 미달',
                'ASYNC_FAIL_PAYMENT' => '결제 승인 실패',
                'ASYNC_LONG_WAIT_PAYMENT' => '결제 승인 실패'
            ];
            $translatedReason = $reasonMapping[$returnReason] ?? $returnReason;
            $reason = $translatedReason . '. ' . $data['return']['returnDetailedReason'];
            $claimRequestDate = new DateTime($data['return']['claimRequestDate']);
            $formattedClaimRequestDate = $claimRequestDate->format('Y-m-d H:i:s');
            $result[] = [
                'requestType' => 'REFUND',
                'reasonType' => $reasonType,
                'reason' => $reason,
                'productOrderNumber' => $data['productOrder']['productOrderId'],
                'quantity' => $data['productOrder']['quantity'],
                'newProductOrderNumber' => $data['productOrder']['claimId'],
                'receiverName' => $data['productOrder']['shippingAddress']['name'],
                'receiverPhone' => $receiverPhone,
                'receiverAddress' => $data['productOrder']['shippingAddress']['baseAddress'] . ' ' . $data['productOrder']['shippingAddress']['detailedAddress'],
                'createdAt' => $formattedClaimRequestDate
            ];
        }
        return $result;
    }
    private function getAccounts()
    {
        return DB::table('smart_store_accounts')
            // ->where('partner_id', $id)
            // ->where('partner_id', 3)
            ->where('is_active', 'ACTIVE')
            ->get();
    }
    private function completeExchangePickup($account, $productOrderId) // 교환 수거 완료
    {
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/exchange/collect/approve';
        $data = [];
        return $this->ssac->builder($account, $contentType, $method, $url, $data);
    }
    private function reshipExchange($account, $productOrderId, $deliveryCompany, $deliveryTrackingNumber) // 교환 재배송 처리
    {
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/exchange/dispatch';
        $data = [
            'reDeliveryMethod' => 'DELIVERY', //택배만 하기 때문
            'reDeliveryCompany' => $deliveryCompany,
            'reDeliveryTrackingNumber' => $deliveryTrackingNumber,
        ];
        return $this->ssac->builder($account, $contentType, $method, $url, $data);
    }
    private function rejectExchangeRequest($account, $productOrderId, $rejectExchangeReason) // 교환 거부(재배송) 처리
    {
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/' . $productOrderId . '/claim/exchange/reject';
        $data = [
            'rejectExchangeReason' => $rejectExchangeReason
        ];
        return $this->ssac->builder($account, $contentType, $method, $url, $data);
    }
}
