<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\ApiController;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoupangShipmentController extends Controller
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
            $singleOrder = $this->getSingleOrder($account, $partnerOrder->product_order_number); //발주서 단건 조회
            // setProduct를 하면 묶음배송번호가 변경됨으로 이거를 이용해서 송장번호 입력해야함
            $orderId = $partnerOrder->order_number;
            $shipmentBoxId = $partnerOrder->product_order_number;
            $isCancelOrder = $this->getIsCancelOrder($account, $orderId, $shipmentBoxId);
            if ($isCancelOrder['status']) {
                //강제출고처리
                $forceResult = $this->forceShipOrder($account, $isCancelOrder['receiptId'], $deliveryCompany->coupang, $trackingNumber);
                if (!$forceResult['status']) {
                    return [
                        'status' => false,
                        'message' => '쿠팡 강제 출고 중 오류가 발생하였습니다.',
                        'data' => $forceResult,
                    ];
                }
                return [
                    'status' => true,
                    'message' => '쿠팡 강제 출고에 성공하였습니다.',
                    'data' => $forceResult,
                ];
            }
            if ($singleOrder['status']) {
                $vendorItemId = $singleOrder['data']['data']['orderItems'][0]['vendorItemId'];
                $responseApi = $this->postApi($account, $shipmentBoxId, $orderId, $deliveryCompany->coupang, $trackingNumber, $vendorItemId);
                if (!$responseApi['status']) {
                    return [
                        'status' => false,
                        'message' => '쿠팡 송장번호 입력 중 오류가 발생하였습니다.',
                        'data' => $responseApi,
                    ];
                }
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
    private function forceShipOrder($account, $receiptId, $deliveryCompanyCode, $invoiceNumber)
    {
        $contentType = 'application/json;charset=UTF-8';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/returnRequests/' . $receiptId . '/completedShipment';
        $data = [
            'vendorId' => $account->code,
            'receiptId' => $receiptId,
            'deliveryCompanyCode' => $deliveryCompanyCode,
            'invoiceNumber' => $invoiceNumber,
        ];
        return $this->ssac->putBuilder($account->access_key, $account->secret_key, $contentType, $path,  $data);
    }
    private function getIsCancelOrder($account, $orderId, $shipmentBoxId)
    {
        $contentType = 'application/json';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/returnRequests';
        $startDate = (new DateTime('now - 4 days'))->format('Y-m-d');
        $endDate = (new DateTime('now'))->format('Y-m-d');
        $baseQuery = [
            'createdAtFrom' => $startDate,
            'createdAtTo' => $endDate,
            'orderId' => $orderId
        ];
        $queryString = http_build_query($baseQuery);
        $controller = new ApiController();
        $response =  $controller->getBuilder($account->access_key, $account->secret_key, $contentType, $path, $queryString);
        $receiptId = 0;
        $cancelCount = 0;
        if (!isset($response['data']['data'])) return [
            'status' => false,
            'message' => '쿠팡 주문 조회가 되지 않습니다.'
        ];
        foreach ($response['data']['data'] as $orderItem) {
            if ($orderItem['returnItems'][0]['shipmentBoxId'] == $shipmentBoxId) {
                $receiptId = $orderItem['receiptId'];
                $cancelCount = $orderItem['returnItems'][0]['cancelCount'];
                break;
            }
        }
        if ($cancelCount === 0) return [
            'status' => false,
            'message' => '주문 취소 건이 아닙니다.'
        ];
        return [
            'status' => true,
            'receiptId' => $receiptId
        ];
    }

    private function postApi($account, $shipmentBoxId, $orderId, $deliveryCompanyCode, $invoiceNumber, $vendorItemId)
    {
        $method = 'POST';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/orders/invoices';
        $contentType = 'application/json;charset=UTF-8';
        $data = [
            'vendorId' => $account->code,
            'orderSheetInvoiceApplyDtos' => [
                [
                    'shipmentBoxId' => $shipmentBoxId,
                    'orderId' => $orderId,
                    'deliveryCompanyCode' => $deliveryCompanyCode, //택배사
                    'invoiceNumber' => $invoiceNumber, //송장번호
                    'vendorItemId' => $vendorItemId, //data - orderItems - vendorItemId
                    'splitShipping' => false,
                    'preSplitShipped' => false, //처음 orderId의 처음 분리배송이면 false, 나머지는 true
                    'estimatedShippingDate' => $this->getDispatchDate()
                ]
            ]
        ];
        return $this->ssac->builder($account->access_key, $account->secret_key, $method, $contentType, $path, $data);
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
        return DB::table('coupang_accounts')
            ->where('id', $accountId)
            ->where('is_active', 'ACTIVE')
            ->first();
    }
    private function getDispatchDate()
    {
        $dateTime = new DateTime('now', new DateTimeZone('Asia/Seoul'));
        $dateTime->modify('+3 days'); // 현재 날짜로부터 3일 뒤로 설정
        return $dateTime->format('Y-m-d\TH:i:s.vP');
    }
}
