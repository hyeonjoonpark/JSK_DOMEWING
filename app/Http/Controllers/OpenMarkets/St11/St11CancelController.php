<?php

namespace App\Http\Controllers\OpenMarkets\St11;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class St11CancelController extends Controller
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
        $account = DB::table('st11_accounts')
            ->where('id', $partnerOrder->account_id)
            ->first();
        list($ordPrdSeq, $dlvNo) = explode('/', $partnerOrder->product_order_number);
        $method = 'GET'; //사유 넘겨주기
        $remark = '배송 지연이 예상됨으로 취소처리하였습니다. 죄송합니다.';
        $url = 'https://api.11st.co.kr/rest/claimservice/reqrejectorder/' . $partnerOrder->order_number . '/' . $ordPrdSeq . '/06/' . $remark;
        /*
        배송업체
→ 06 : 배송 지연 예상
→ 07 : 상품/가격 정보 잘못 입력
→ 08 : 상품 품절(전체옵션)
→ 09 : 옵션 품절(해당옵션)
→ 10 : 고객변심
→ 99 : 기타
        */
        $apiKey = $account->access_key;
        $response = $this->ssac->builder($apiKey, $method, $url);
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
