<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Support\Facades\DB;

class CoupangCancelController extends Controller
{
    private $ssac;
    public function __construct()
    {
        $this->ssac = new ApiController();
    }
    public function index($productOrderNumber)
    {
        $order = DB::table('orders')
            ->where('product_order_number', $productOrderNumber)
            ->first();
        $partnerOrder = DB::table('partner_orders')
            ->where('order_id', $order->id)
            ->first();
        $cart = DB::table('carts')
            ->where('id', $order->cart_id)
            ->first();
        $account = DB::table('coupang_accounts')
            ->where('id', $partnerOrder->account_id)
            ->first();
        $singleOrder = $this->getSingleOrder($account, $partnerOrder->product_order_number); //발주서 단건 조회
        if (!$singleOrder['status']) {
            return [
                'status' => true,
                'message' => '쿠팡 주문의 상태가 변경되었습니다.',
                'error' => $singleOrder
            ];
        }
        $receiptDetails = $this->fetchReceiptDetails($account, $partnerOrder->order_number, $partnerOrder->product_order_number);
        if ($receiptDetails['status']) {
            $response = $this->acceptCancel($account, $receiptDetails['receiptId'], $receiptDetails['cancelCount']); //취소 승인 api
            if (!$response['status']) return [
                'status' => false,
                'message' => '쿠팡 취소 주문이어서 취소 처리중 취소 승인에 실패하였습니다.',
                'data' => $response
            ];
            return [
                'status' => true,
                'message' => '쿠팡에서 취소요청인 주문이어서 취소요청 승인하였습니다.',
                'data' => $response,
                'cancelled' => true
            ];
        }
        $vendorItemId = $singleOrder['data']['data']['orderItems'][0]['vendorItemId'];
        return $this->cancelOrder($account, $vendorItemId, $cart->quantity, $partnerOrder->order_number);
    }
    public function cancelOrder($account, $vendorItemId, $quantity, $orderId)
    {
        $method = 'POST';
        $path = '/v2/providers/openapi/apis/api/v5/vendors/' . $account->code . '/orders/' . $orderId . '/cancel';
        $contentType = 'application/json;charset=UTF-8';
        $data = [
            'orderId' => $orderId,
            'vendorItemIds' => [$vendorItemId],
            'receiptCounts' => [$quantity],
            'bigCancelCode' => 'CANERR',
            'middleCancelCode' => 'CCPNER',
            /*
            CCTTER // 재고 연동 오류 : 재고 문제로 취소가 발생하는 경우
            CCPNER // 제휴사이트 오류 : 주소 문제로 고객 배송지 생성 불가시 취소 되는 오류
            CCPRER // 가격등재오류 : 양사간 상품 가격오류 발생시 취소 되는 오류
            상품준비중 상태의 상품 취소(출고중지완료) 시에는 입력값과 상관없이 사유 카테고리가 각각 "배송불만", "품절"로 고정됩니다.
            취소 상세 사유는 "파트너 API 강제 취소"로 기록됩니다.
            */
            'userId' => $account->username,
            'vendorId' => $account->code
        ];
        $response =  $this->ssac->builder($account->access_key, $account->secret_key, $method, $contentType, $path, $data);
        if (!$response['status']) return [
            'status' => false,
            'message' => '주문취소에 실패하였습니다.',
            'error' => $response['error']
        ];
        return [
            'status' => true,
            'message' => '주문취소에 성공하였습니다.',
            'data' => $response
        ];
    }
    private function getSingleOrder($account, $productOrderNumber) //발주서 단건 조회
    {
        $contentType = 'application/json;charset=UTF-8';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/ordersheets' . '/' .  $productOrderNumber;
        return $this->ssac->getBuilder($account->access_key, $account->secret_key, $contentType, $path);
    }
    private function fetchReceiptDetails($account, $orderId, $shipmentBoxId)
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
        $response =  $controller->getBuilder($account->access_key, $account->secret_key, $contentType, $path, $queryString); //발주서 단건조회
        $receiptId = 0;
        $cancelCount = 0;
        $purchaseCount = 0;
        if (!isset($response['data']['data'])) return [
            'status' => false,
            'message' => '쿠팡 주문 조회가 되지 않습니다.'
        ];
        foreach ($response['data']['data'] as $orderItem) {
            if ($orderItem['returnItems'][0]['shipmentBoxId'] == $shipmentBoxId) {
                $receiptId = $orderItem['receiptId'];
                $cancelCount = $orderItem['returnItems'][0]['cancelCount'];
                $purchaseCount = $orderItem['returnItems'][0]['purchaseCount'];
                break;
            }
        }
        if ($cancelCount === 0) return [
            'status' => false,
            'message' => '주문 취소 건이 아닙니다.'
        ];
        return [
            'status' => true,
            'receiptId' => $receiptId,
            'cancelCount' => $cancelCount,
            'purchaseCount' => $purchaseCount
        ];
    }
    private function acceptCancel($account, $receiptId, $cancelCount)
    {
        $contentType = 'application/json;charset=UTF-8';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/returnRequests/' . $receiptId . '/stoppedShipment';
        $data = [
            'vendorId' => $account->code,
            'receiptId' => $receiptId,
            'cancelCount' => $cancelCount
        ];
        return $this->ssac->putBuilder($account->access_key, $account->secret_key, $contentType, $path, $data);
    }
}
