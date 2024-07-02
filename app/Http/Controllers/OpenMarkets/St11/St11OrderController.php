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
    public function index($id = 13) //null로수정 partners를 위함
    {
        if ($id == null) {
            $id = Auth::guard('partner')->id();
        }
        $orderList = $this->getOrderList($id);

        return $orderList;
    }
    private function getOrderList($id)
    {
        $accounts = $this->getAccounts($id);
        if (!$accounts) {
            return false;
        }
        $orderList = [];
        $startDate = (new DateTime('now - 4 days'))->format('YmdHi');
        $endDate = (new DateTime('now'))->format('YmdHi');
        $method = 'GET';
        $url = 'https://api.11st.co.kr/rest/ordservices/complete/' . $startDate . '/' . $endDate;
        foreach ($accounts as $account) {
            $apiKey = $account->access_key;
            $builderResult = $this->ssac->orderBuilder($apiKey, $method, $url); //날짜별 결제완료 주문내역 조회
            if ($builderResult['status'] === false) continue; //오류는 그냥 넘겨
            $orderList[] = $this->getProcessOrder($builderResult, $account);
        }
        return [
            'status' => true,
            'message' => '성공하였습니다.',
            'data' => $orderList
        ];
    }
    private function getProcessOrder($data, $account)
    {
        $orderList = [];
        if (isset($data['data']['ns2:order']) && is_array($data['data']['ns2:order'])) {
            foreach ($data['data']['ns2:order'] as $order) {
                $orderList[] = [
                    'market' => '11번가',
                    'marketEngName' => 'st11',
                    'orderId' => $order['ordNo'],
                    'productOrderId' => $order['ordPrdSeq'],
                    'orderName' => $order['ordNm'],
                    'productName' => $order['prdNm'],
                    'quantity' => $order['ordQty'],
                    'unitPrice' => $order['selPrc'],
                    'totalPaymentAmount' => $order['ordPayAmt'],
                    'deliveryFeeAmount' => $order['lstDlvCst'],
                    'productOrderStatus' => '결제완료',
                    'orderDate' => (new DateTime($order['ordDt']))->format('Y-m-d H:i:s'),
                    'receiverName' => $order['rcvrNm'],
                    'receiverPhone' => $order['rcvrPrtblNo'],
                    'postCode' => $order['rcvrMailNo'],
                    'address' => $order['rcvrBaseAddr'] . ' ' . $order['rcvrDtlsAddr'],
                    'addressName' => '기본배송지',
                    'productCode' => $order['sellerPrdCd'],
                    'remark' => $order['ordDlvReqCont'],
                    'accountId' => $account->id
                ];
            }
        }
        return $orderList;
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
            ->get();
    }
}
