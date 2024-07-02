<?php

namespace App\Http\Controllers\SmartStore;

use App\Http\Controllers\Controller;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SmartStoreShipmentController extends Controller
{
    private $ssac;

    public function __construct()
    {
        $this->ssac = new SmartStoreApiController();
    }

    public function index(Request $request)
    {
        try {
            $trackingNumber = $request->input('trackingNumber');
            $deliveryCompanyId = $request->input('deliveryCompanyId');
            $productOrderNumber = $request->input('productOrderNumber');
            $deliveryCompany = $this->getDeliveryCompany($deliveryCompanyId);
            $order = $this->getOrder($productOrderNumber);
            $productOrder = $this->getProductOrder($order->id);
            $account = $this->getAccount($productOrder->account_id);
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '데이터 조회 중 오류가 발생했습니다: ' . $e->getMessage(),
            ];
        }
        $isCancelOrder = $this->getIsCancelOrder($account, $productOrderNumber);
        if (!$isCancelOrder['status']) return [
            'status' => false,
            'message' => $isCancelOrder['message']
        ];

        $responseApi = $this->postApi($account, $productOrder->product_order_number, $deliveryCompany->smart_store, $trackingNumber);
        if (!$responseApi['status']) {
            return [
                'status' => false,
                'message' => $responseApi['message'],
            ];
        }
        return [
            'status' => true,
            'data' => $responseApi
        ];
    }
    private function getIsCancelOrder($account, $productOrderNumber)
    {
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/query';
        $data = ['productOrderIds' => [$productOrderNumber]];
        $controller = new SmartStoreApiController();
        $builderResponse =  $controller->builder($account, $contentType, $method, $url, $data); //조회해서 상태확인
        if (isset($builderResponse['data']['data'][0]['cancel'])) return [
            'status' => false,
            'message' => '취소요청이 들어온 주문입니다. 강제출고를 위해 파트너스 회원에게 연락바랍니다.'
        ];
        return [
            'status' => true,
        ];
    }
    private function postApi($account, $productOrderId, $deliveryCompany, $trackingNumber)
    {
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/dispatch';
        $dispatchDate = $this->getDispatchDate();
        $data = [
            'dispatchProductOrders' => [
                [
                    'productOrderId' => $productOrderId,
                    'deliveryMethod' => 'DELIVERY',
                    'deliveryCompanyCode' => $deliveryCompany,
                    'trackingNumber' => $trackingNumber,
                    'dispatchDate' => $dispatchDate,
                ]
            ]
        ];
        return $this->ssac->builder($account, $contentType, $method, $url, $data);
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
    private function getProductOrder($orderId)
    {
        return DB::table('partner_orders as ps')
            ->where('ps.order_id', $orderId)
            ->first();
    }
    private function getAccount($accountId)
    {
        return DB::table('smart_store_accounts')
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
