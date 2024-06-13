<?php

namespace App\Http\Controllers\OpenMarkets\St11;

use App\Http\Controllers\Controller;

class St11OrderController extends Controller
{
    private $ssac;
    public function __construct()
    {
        $this->ssac = new ApiController();
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
}
