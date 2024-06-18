<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\ApiController;
use App\Http\Controllers\OpenMarkets\St11\ApiController as St11ApiController;
use App\Http\Controllers\SmartStore\SmartStoreApiController;
use App\Http\Controllers\WingController;
use DateTime;
use Illuminate\Support\Facades\DB;

class OpenMarketSetAwaitingController extends Controller
{
    public function setAwaitingShipmentStatus($productOrderNumber)
    {
        $order = DB::table('orders')
            ->where('product_order_number', $productOrderNumber)
            ->where('requested', 'N')
            ->where('delivery_status', 'PENDING')
            ->whereNotIn('type', ['CANCELLED'])
            ->first();
        if (!$order) {
            return [
                'status' => false,
                'message' => '배송 대기 중으로 변경 가능한 주문이 아닙니다.',
            ];
        }
        $balance = $this->checkBalance($order);
        if (!$balance['status']) return $balance;
        $openMarket = DB::table('orders as o')
            ->join('partner_orders as po', 'o.id', '=', 'po.order_id')
            ->join('vendors as v', 'po.vendor_id', '=', 'v.id')
            ->where('o.product_order_number', $productOrderNumber)
            ->where('v.is_active', 'ACTIVE')
            ->select('v.*')
            ->first(['v.name_eng', 'v.name']);
        if ($openMarket) {
            $method = 'call' . ucfirst($openMarket->name_eng) . 'CheckApi';
            $updateApiResult = $this->$method($order);
            if ($updateApiResult['status'] === false) {
                return $updateApiResult;
            }
            if ($updateApiResult['cancelled']) return [
                'status' => true,
                'message' => $updateApiResult['message']
            ];
        }
        $updated = DB::table('orders')
            ->where('product_order_number', $productOrderNumber)
            ->update(['requested' => 'Y']);
        if ($updated) {
            return response()->json([
                'status' => true,
                'message' => '주문 상태가 배송 대기 중으로 변경되었습니다.',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => '주문 상태 변경에 실패했습니다.',
            ]);
        }
    }
    private function callSmart_storeCheckApi($order)
    {
        $partnerOrder = DB::table('partner_orders')
            ->where('order_id', $order->id)
            ->first();
        $account = DB::table('smart_store_accounts as ssa')
            ->where('ssa.id', $partnerOrder->account_id)
            ->first();
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/query';
        $data = ['productOrderIds' => [$partnerOrder->product_order_number]];
        $controller = new SmartStoreApiController();
        $builderResponse =  $controller->builder($account, $contentType, $method, $url, $data); //조회해서 상태확인
        if (isset($builderResponse['data']['data'][0]['cancel'])) { //취소상태이면 취소 승인 진행
            $cancelResponse = $this->smartStoreCancelApi($controller, $account, $contentType, $method, $partnerOrder->product_order_number); //취소 승인 api
            if ($cancelResponse['status'] === false) { //취소 승인 실패
                return [
                    'status' => false,
                    'message' => '스마트스토어 취소요청인 주문인데 취소요청 승인에 실패하였습니다.',
                    'data' => $cancelResponse,
                    'cancelled' => false
                ];
            }
            $updated = $this->updateCancel($order);
            if (!$updated['status']) return [
                'status' => false,
                'message' => '업데이트에 실패하였습니다.',
                'cancelled' => false
            ];
            return [
                'status' => true,
                'message' => '스마트스토어에서 취소요청인 주문이어서 취소요청 승인하였습니다.',
                'data' => $builderResponse,
                'cancelled' => true
            ];
        }
        return [
            'status' => true,
            'message' => '스마트스토어 취소 요청건이 아닙니다.',
            'cancelled' => false
        ];
    }
    private function callCoupangCheckApi($order)
    {
        $partnerOrder = DB::table('partner_orders')
            ->where('order_id', $order->id)
            ->first();
        $account = DB::table('coupang_accounts as ca')
            ->where('ca.id', $partnerOrder->account_id)
            ->first();
        $contentType = 'application/json';
        $controller = new ApiController();
        $orderId = $partnerOrder->order_number;
        $shipmentBoxId = $partnerOrder->product_order_number;
        $receiptDetails = $this->fetchReceiptDetails($account, $orderId, $shipmentBoxId); //orderId로 반품건 조회 조회 결과에
        if ($receiptDetails['status']) {
            $response = $this->coupangCancelApi($controller, $account, $contentType, $receiptDetails['receiptId'], $receiptDetails['cancelCount']); //취소 승인 api
            if (!$response['status']) return [
                'status' => false,
                'message' => '취소 승인에 실패하였습니다.',
                'data' => $response
            ];
            $updated = $this->updateCancel($order);
            if (!$updated['status']) return [
                'status' => false,
                'message' => '업데이트에 실패하였습니다.',
                'cancelled' => false
            ];
            return [
                'status' => true,
                'message' => '쿠팡에서 취소요청인 주문이어서 취소요청 승인하였습니다.',
                'data' => $response,
                'cancelled' => true
            ];
        }
        return [
            'status' => true,
            'message' => '쿠팡 취소 요청건이 아닙니다.',
            'cancelled' => false
        ];
    }
    private function callSt11CheckApi($order)
    {
        $partnerOrder = DB::table('partner_orders')
            ->where('order_id', $order->id)
            ->first();
        $account = DB::table('coupang_accounts as ca')
            ->where('ca.id', $partnerOrder->account_id)
            ->first();
        $apiKey = $account->access_key;
        $controller = new St11ApiController();
        $method = 'GET';
        $startDate = new DateTime('now - 4 days');
        $endDate = new DateTime('now');
        $formattedStartDate = $startDate->format('YmdHi');
        $formattedEndDate = $endDate->format('YmdHi');
        $url = 'http://api.11st.co.kr/rest/claimservice/cancelorders/' . $formattedStartDate . '/' . $formattedEndDate;
        $builderResult = $controller->builder($apiKey, $method, $url); //날짜별 취소내역 조회


        // $this->st11CancelApi($apiKey);
    }
    private function st11CancelApi($apiKey, $ordPrdCnSeq, $ordNo, $ordPrdSeq)
    {
        $controller = new St11ApiController();
        $method = 'GET';
        $url = 'http: //api.11st.co.kr/rest/claimservice/cancelreqconf/[ordPrdCnSeq]/[ordNo]/[ordPrdSeq]';

        $builderResult = $controller->builder($apiKey, $method, $url); //주문취소승인
    }
    private function smartStoreCancelApi($controller, $account, $contentType, $method, $productOrderNumber)
    {
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/' . $productOrderNumber . '/claim/cancel/approve';
        $data = [
            'productOrderId' => $productOrderNumber
        ];
        return $controller->builder($account, $contentType, $method, $url, $data);
    }
    private function fetchReceiptDetails($account, $orderId, $shipmentBoxId)
    {
        $contentType = 'application/json';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/returnRequests';
        $startDate = new DateTime('now - 7 days');
        $endDate = new DateTime('now');
        $baseQuery = [
            'createdAtFrom' => $startDate->format('Y-m-d'),
            'createdAtTo' => $endDate->format('Y-m-d'),
            'orderId' => $orderId
        ];
        $queryString = http_build_query($baseQuery);
        $controller = new ApiController();
        $response =  $controller->getBuilder($account->access_key, $account->secret_key, $contentType, $path, $queryString); //발주서 단건조회
        $receiptId = 0;
        $cancelCount = 0;
        $purchaseCount = 0;
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
    private function coupangCancelApi($controller, $account, $contentType, $receiptId, $cancelCount)
    {
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/returnRequests/' . $receiptId . '/stoppedShipment';
        $data = [
            'vendorId' => $account->code,
            'receiptId' => $receiptId,
            'cancelCount' => $cancelCount
        ];
        return $controller->putBuilder($account->access_key, $account->secret_key, $contentType, $path, $data);
    }
    private function updateCancel($order)
    {
        DB::beginTransaction();
        try {
            // orders 테이블 업데이트
            DB::table('orders')
                ->where('product_order_number', $order->product_order_number)
                ->where('delivery_status', 'PENDING')
                ->update([
                    'type' => 'CANCELLED',
                    'remark' => '오픈마켓 주문취소 요청으로 인해 취소되었습니다.'
                ]);
            // 트랜잭션 커밋
            DB::commit();
            return [
                'status' => true,
                'message' => '오픈마켓 주문취소 요청으로 인해 취소되었습니다.',
            ];
        } catch (\Exception $e) {
            // 트랜잭션 롤백
            DB::rollBack();
            return [
                'status' => false,
                'message' => '오픈마켓 주문취소 중 오류가 발생하였습니다.',
                'error' => $e->getMessage(),
            ];
        }
    }
    private function checkBalance($order)
    {
        $cart = DB::table('carts')
            ->where('id', $order->cart_id)
            ->first();
        $wc = new WingController();
        $balance = $wc->getBalance($cart->member_id);
        if ($balance < 0) return [
            'status' => false,
            'message' => '해당 주문을 처리하기 위한 잔액이 부족합니다',
            'data' => $cart->member_id
        ];
        return [
            'status' => true,
            'message' => '잔액이 충분합니다.'
        ];
    }
}
