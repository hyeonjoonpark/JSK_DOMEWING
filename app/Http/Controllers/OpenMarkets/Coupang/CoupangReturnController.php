<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\OpenMarketExchangeRefundController;
use DateTime;
use Illuminate\Support\Facades\DB;

class CoupangReturnController extends Controller
{
    private $ssac;
    public function __construct()
    {
        $this->ssac = new ApiController();
    }
    public function index($partnerId)
    {
        try {
            $accounts = $this->getActiveAccounts($partnerId);
            $customerResponsibilityReasons = $this->getCustomerResponsibilityReasons();
            $results = [];
            foreach ($accounts as $account) {
                $apiResult = $this->getReturnList($account);
                if (!$apiResult['status'] || !isset($apiResult['data']['data'])) continue;
                foreach ($apiResult['data']['data'] as $returnData) {
                    $reasonCode = $returnData['reasonCode'];
                    $reasonType = in_array($reasonCode, $customerResponsibilityReasons) ? '단순변심' : '상품정보와 상이';
                    $results[] = $this->transformReturnData($returnData, $reasonType);
                }
            }
            $openMarketExchangeRefundController = new OpenMarketExchangeRefundController();
            $createResult = [];
            foreach ($results as $result) {
                if (!$this->isExistReturnOrder($result['newProductOrderNumber']))
                    $createResult[] = $openMarketExchangeRefundController->createExchangeRefund($result);
            }
            return ['status' => true, 'message' => '쿠팡환불요청수집에 성공하였습니다', 'data' => $createResult];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => '쿠팡환불요청수집에 에러가 발생하였습니다', 'error' => $e->getMessage()];
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
    private function getReturnList($account)
    {
        $startDate = (new DateTime('now - 4 days'))->format('Y-m-d\TH:i');
        $endDate = (new DateTime('now'))->format('Y-m-d\TH:i');
        $contentType = 'application/json';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/returnRequests';
        $baseQuery = [
            'createdAtFrom' => $startDate,
            'createdAtTo' => $endDate,
            'status' => 'UC'
        ];
        $query = 'searchType=timeFrame&' . http_build_query($baseQuery);
        return $this->ssac->getBuilder($account->access_key, $account->secret_key, $contentType, $path, $query);
    }
    private function transformReturnData($returnData, $reasonType)
    {
        return [
            'requestType' => 'REFUND',
            'reasonType' => $reasonType,
            'reason' => $returnData['reasonCodeText'],
            'productOrderNumber' => $returnData['returnItems'][0]['shipmentBoxId'],
            'quantity' => $returnData['returnItems'][0]['cancelCount'],
            'newProductOrderNumber' => $returnData['receiptId'],
            'receiverName' => $returnData['requesterName'],
            'receiverPhone' => $returnData['requesterPhoneNumber'] ? $returnData['requesterPhoneNumber'] : $returnData['requesterRealPhoneNumber'],
            'receiverAddress' => $returnData['requesterAddress'] . ' ' . $returnData['requesterAddressDetail'],
            'createdAt' => $returnData['createdAt']
        ];
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
        return $this->ssac->putBuilder($accessKey, $secretKey, $contentType, $path, $data);
    }
}
