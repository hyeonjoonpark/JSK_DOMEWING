<?php

namespace App\Http\Controllers\OpenMarkets\St11;

use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class St11OrderController extends Controller
{
    private $ssac;
    public function __construct()
    {
        $this->ssac = new ApiController();
    }
    public function index($id = 13)
    {
        // if ($id == null) {
        //     $id = Auth::guard('partner')->id();
        // }
        $id = 13;
        $orderList = $this->getOrderList($id);
        return $orderList;
    }
    private function getOrderList($id)
    {
        $account = $this->getAccounts($id);
        if (!$account) {
            return false;
        }
        $response = [];
        $startDate = (new DateTime('now - 4 days'))->format('YmdHi');
        $endDate = (new DateTime('now'))->format('YmdHi');

        $method = 'GET';
        $url = 'https://api.11st.co.kr/rest/ordservices/complete/' . $startDate . '/' . $endDate;
        // $url = 'https://api.11st.co.kr/rest/ordservices/complete/20240618837686159'; //실제 주문번호로 조회
        // foreach ($accounts as $account) {
        $apiKey = $account->access_key;
        $builderResult = $this->ssac->orderBuilder($apiKey, $method, $url); //날짜별 주문내역 조회
        // if ($builderResult['status'] === false) continue; //오류는 그냥 넘겨

        // $addrSeq = $builderResult['data']->xpath('//ns2:order')[0]->addrSeq;
        // $confirmResult = $this->confirmOrder($apiKey, $builderResult['ordNo'], $builderResult['ordPrdSeq'], $builderResult['dlvNo']); //상품준비중처리
        // if ($confirmResult['status'] === false) {
        //     return $confirmResult;
        // }
        //상품 준비중 처리까지하면 builderResult를 한쪽에 잘 정렬해서 넣고 return
        $response[] = $builderResult;
        // }
        return [
            'status' => true,
            'message' => '성공하였습니다.',
            'data' => $response
        ];
    }
    private function confirmOrder($apiKey, $ordNo, $ordPrdSeq, $dlvNo)
    {
        $method = 'GET';
        $url = 'https: //api.11st.co.kr/rest/ordservices/reqpackaging/' . $ordNo . '/' . $ordPrdSeq . '/N/0/' . $dlvNo;
        $builderResult = $this->ssac->builder($apiKey, $method, $url);
    }
    private function processData($response, $account)
    {
        return [
            'market' => '11번가',
            'marketEngName' => 'st11',
            //'orderId' => ordNo,
            //'productOrderId' => strval($item['shipmentBoxId']),
            //'orderName' => ordNm,
            //'productName' => $orderItem['vendorItemName'],
            //'quantity' => ordQty,
            //'unitPrice' => $orderItem['salesPrice'],
            //'totalPaymentAmount' => $orderItem['orderPrice'],
            //'deliveryFeeAmount' => $item['shippingPrice'],
            //'productOrderStatus' => '결제완료',
            //'orderDate' => (new DateTime($item['ordDt']))->format('Y-m-d H:i:s'),
            //'receiverName' => rcvrNm,
            //'receiverPhone' => rcvrPrtblNo,
            //'postCode' => rcvrMailNo,
            //'address' => rcvrBaseAddr . rcvrDtlsAddr,
            'addressName' => '기본배송지',
            //'productCode' => sellerPrdCd,
            //'remark' => ordDlvReqCont,
            'accountId' => $account->id
        ];
    }
    private function getAccounts($id)
    {
        return DB::table('st11_accounts')
            ->where('partner_id', $id)
            ->where('is_active', 'ACTIVE')
            ->first();
    }
}
