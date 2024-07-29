<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\OpenMarketExchangeRefundController;
use DateTime;
use Illuminate\Support\Facades\DB;

class CoupangReturnController extends Controller
{
    public function index($partnerId)
    {
        try {
            $accounts = $this->getActiveAccounts($partnerId); //모든 계정 가져오기
            $customerResponsibilityReasons = $this->getCustomerResponsibilityReasons();
            $results = [];
            foreach ($accounts as $account) {
                $apiResult = $this->getReturnList($account);
                if (!$apiResult['status'] || !isset($apiResult['data']['data'])) continue;
                foreach ($apiResult['data']['data'] as $returnData) { //응답이 올바르면 정보삽입
                    $reasonCode = $returnData['reasonCode'];
                    $reasonType = in_array($reasonCode, $customerResponsibilityReasons) ? '단순변심' : '상품정보와 상이';
                    $results[] = $this->transformReturnData($returnData, $reasonType);
                }
            }
            $createResult = [];
            foreach ($results as $result) {
                if (!$this->isExistReturnOrder($result['newProductOrderNumber'])) {
                    $openMarketExchangeRefundController = new OpenMarketExchangeRefundController();
                    $createResult[] = $openMarketExchangeRefundController->createExchangeRefund($result);
                }
            }
            return ['status' => true, 'message' => '쿠팡환불요청수집에 성공하였습니다', 'data' => $createResult];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '쿠팡환불요청수집에 에러가 발생하였습니다',
                'error' => $e->getMessage(),
            ];
        }
    }
    private function isExistReturnOrder($newProductOrderNumber)
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
    private function getReasonInKorean($code)
    {
        $reasons = [
            'CHANGEMIND' => '필요 없어짐 (단순 변심)',
            'DIFFERENTOPT' => '색상/ 사이즈가 기대와 다름(같은 상품의 다른 옵션으로 교환)',
            'DONTLIKESIZECOLOR' => '색상, 사이즈가 기대와 다름',
            'CHEAPER' => '다른 사이트의 가격이 더 저렴함',
            'WRONGOPT' => '상품의 옵션 선택을 잘못함',
            'DELIVERYLATER' => '배송 예정일이 예상보다 늦음',
            'WRONGADDRESS' => '배송지 입력 실수',
            'REORDER' => '상품을 추가하여 재주문',
            'OTHERS' => '기타',
            'DELIVERYSTOP' => '배송흐름이 멈춤',
            'CARRIERLOST' => '택배사 상품 분실',
            'LOST' => '배송주소 오배송(원인파악 불가 포함)',
            'PARTIALMISS' => '주문상품 중 일부가 배송되지 않음',
            'COMPOMISS' => '상품의 구성품, 부속품이 제대로 들어있지 않음',
            'LATEDELIVERED' => '상품이 늦게 배송됨',
            'SKUOOSRESHIP' => 'SKU OOS로 나머지 상품 재출고',
            'DELIVERYSTUCK' => '배송 상태가 멈춰 있음',
            'LIKELYDELAY' => '배송지연이 예상됨',
            'DAMAGED' => '상품이 파손되어 배송됨',
            'DEFECT' => '상품 결함/기능에 이상이 있음',
            'INACCURATE' => '실제 상품이 상품 설명에 써있는 것과 다름',
            'BOTHDAMAGED' => '포장과 상품 모두 훼손됨',
            'SHIPBOXOK' => '포장은 괜찮으나 상품이 파손됨',
            'UNABLEBOOK' => '티켓 상품 예약 불가능',
            'TICKETNOUSE' => '티켓 상품 지점 휴업/ 폐점으로 사용 불가능',
            'PRICEERROR' => '잘못된 가격 기재',
            'ITEMNAMEERR' => '잘못된 상품명 기재',
            'WRONGDELIVERY' => '내가 주문한 상품과 아예 다른 상품이 배송됨',
            'WRONGSIZECOL' => '내가 주문한 상품과 다른 색상/사이즈의 상품이 배송됨',
            'OOSSELLER' => '업체로부터 품절되었다고 연락 받음',
            'UNUSEDTICKET' => '티켓 상품의 미사용 환불 취소',
            'DUPLICATE' => 'Abusing 의심 중복구매 취소',
            'SKUOOSCAN' => 'SKU OOS 취소',
            'SYSTEMERROR' => '시스템 오류',
            'SYSTEMINFO_ERROR' => '상품 정보가 잘못 노출됨(쿠팡 시스템 오류)',
            'EXITERROR' => '주문 이탈',
            'PAYERROR' => '결제 오류',
            'PARTNERERROR' => '제휴사이트 오류',
            'REGISTERROR' => '쿠폰 등록 오류',
            'NOTPURCHASABLE' => '구매 불가능'
        ];

        return $reasons[$code] ?? '';
    }
    private function getReturnList($account)
    {
        $startDate = (new DateTime('now - 4 days'))->format('Y-m-d\TH:i');
        $endDate = (new DateTime('now'))->format('Y-m-d\TH:i');
        $contentType = 'application/json';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/returnRequests';
        $baseQuery = [
            'createdAtFrom' => $startDate,
            'createdAtTo' => $endDate,
            'status' => 'UC',
            'cancelType' => 'RETURN'
        ];
        $query = 'searchType=timeFrame&' . http_build_query($baseQuery);
        $ac = new ApiController();
        return $ac->getBuilder($account->access_key, $account->secret_key, $contentType, $path, $query);
    }
    private function transformReturnData($returnData, $reasonType)
    {
        $reason = $this->getReasonInKorean($returnData['reasonCode']);
        return [
            'requestType' => 'REFUND',
            'reasonType' => $reasonType,
            'reason' => $reason . '. ' . $returnData['reasonCodeText'],
            'productOrderNumber' => $returnData['returnItems'][0]['shipmentBoxId'],
            'quantity' => $returnData['returnItems'][0]['cancelCount'],
            'newProductOrderNumber' => $returnData['receiptId'],
            'receiverName' => $returnData['requesterName'],
            'receiverPhone' => $returnData['requesterPhoneNumber'] ?? $returnData['requesterRealPhoneNumber'] ?? '01000000000',
            'receiverAddress' => $returnData['requesterAddress'] . ' ' . $returnData['requesterAddressDetail'],
            'createdAt' => $returnData['createdAt']
        ];
    }
    public function isCancelOrder($account, $receiptId)
    {
        $contentType = 'application/json;charset=UTF-8';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/returnRequests/' . $receiptId;
        $ac = new ApiController();
        return $ac->getBuilder($account->access_key, $account->secret_key, $contentType, $path);
    }
    public function confirmReturnReceipt($account, $receiptId)
    {
        $accessKey = $account->access_key;
        $secretKey = $account->secret_key;
        $contentType = 'application/json;charset=UTF-8';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/returnRequests/' . $receiptId . '/receiveConfirmation';
        $data = [
            'vendorId' => $account->code,
            'receiptId' => $receiptId
        ];
        $ac = new ApiController();
        $returnStockConfirmation = $ac->putBuilder($accessKey, $secretKey, $contentType, $path, $data);
        if (!$returnStockConfirmation['status']) return [
            'status' => false,
            'message' => '상품 입고처리에 문제가 발생하였습니다.'
        ];

        sleep(2);

        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/returnRequests/' . $receiptId . '/approval';
        return $ac->putBuilder($accessKey, $secretKey, $contentType, $path, $data);
    }
}
