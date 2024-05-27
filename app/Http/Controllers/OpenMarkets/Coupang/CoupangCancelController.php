<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
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
}
