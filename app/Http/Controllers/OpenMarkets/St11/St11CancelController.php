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
    public function cancelOrder($account, $ordNo, $ordPrdSeq)
    {
        $method = 'GET';
        $url = 'https://api.11st.co.kr/rest/claimservice/reqrejectorder/' . $ordNo . '/' . $ordPrdSeq . '/06/[ordCnDtlsRsn]';
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
