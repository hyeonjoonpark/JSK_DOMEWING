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
            $wingTransaction = $this->getWingTransaction($order->wing_transaction_id);
            $domewingPartner = $this->getDomewingPartner($wingTransaction->member_id);
            $partner = $this->getPartner($domewingPartner->partner_id);
            $account = $this->getAccount($partner->id);
            $productOrder = $this->getProductOrder($order->id);
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '데이터 조회 중 오류가 발생했습니다: ' . $e->getMessage(),
            ];
        }

        $responseApi = $this->postApi($account, $productOrder->product_order_number, $deliveryCompany, $trackingNumber);

        if ($responseApi['response'] == false) {
            return [
                'status' => false,
                'message' => $responseApi['message'],
            ];
        }

        return $this->update($order->id, $deliveryCompanyId, $trackingNumber);
    }

    private function getDeliveryCompany($deliveryCompanyId)
    {
        return DB::table('delivery_companies as dc')
            ->where('dc.id', $deliveryCompanyId)
            ->select('smart_store')
            ->first();
    }

    private function getOrder($productOrderNumber)
    {
        return DB::table('orders')
            ->where('product_order_number', $productOrderNumber)
            ->first();
    }

    private function getWingTransaction($wingTransactionId)
    {
        return DB::table('wing_transactions as wt')
            ->where('wt.id', $wingTransactionId)
            ->first();
    }

    private function getDomewingPartner($memberId)
    {
        return DB::table('partner_domewing_accounts as pda')
            ->where('pda.domewing_account_id', $memberId)
            ->where('is_active', 'Y')
            ->first();
    }

    private function getPartner($partnerId)
    {
        return DB::table('partners as p')
            ->where('p.id', $partnerId)
            ->first();
    }

    private function getProductOrder($orderId)
    {
        return DB::table('partner_orders as ps')
            ->where('ps.order_id', $orderId)
            ->first();
    }

    private function postApi($account, $productOrderId, $deliveryCompany, $trackingNumber)
    {
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/last-changed-statuses';
        $dispatchDate = $this->getDispatchDate();
        $data = [
            $productOrderId,
            'deliveryMethod' => 'DELIVERY',
            $deliveryCompany,
            $trackingNumber,
            $dispatchDate,
        ];
        return $this->ssac->builder($account, $contentType, $method, $url, $data);
    }

    private function update($orderId, $deliveryCompanyId, $trackingNumber)
    {
        try {
            DB::table('orders')
                ->where('id', $orderId)
                ->update([
                    'tracking_number' => $trackingNumber,
                    'delivery_company_id' => $deliveryCompanyId,
                    'delivery_status' => 'COMPLETE',
                ]);
            return [
                'status' => true,
                'message' => '송장번호 입력에 성공하였습니다',
                'data' => []
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    private function getAccount($id)
    {
        return DB::table('smart_store_accounts')
            ->where('partner_id', $id)
            ->first();
    }

    private function getDispatchDate()
    {
        $dateTime = new DateTime('now', new DateTimeZone('Asia/Seoul'));
        $dateTime->modify('+3 days'); // 현재 날짜로부터 3일 뒤로 설정
        return $dateTime->format('Y-m-d\TH:i:s.vP');
    }
}
