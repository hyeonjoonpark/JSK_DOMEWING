<?php

namespace App\Http\Controllers\SmartStore;

use App\Http\Controllers\Controller;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // $id = Auth::guard('partner')->id(); //파트너의 id를 받아와야함 그래야 api 쏠때 파트너 id의 토큰을 줌
        $trackingNumber = $request->input('trackingNumber');
        $deliveryCompany = $request->input('deliveryCompany');
        $productOrderId = $request->input('productOrderId');
        $orderId = $request->input('orderId');

        // $account = $this->getAccount($id);
        $responseApi = $this->posetApi($account, $productOrderId, $deliveryCompany, $trackingNumber);
        if ($responseApi['response' == false]) {
            return [
                'status' => false,
                'message' => $responseApi['message'],
            ];
        }
        return $this->create($orderId, $deliveryCompany, $trackingNumber);
    }
    private function posetApi($account, $productOrderId, $deliveryCompany, $trackingNumber)
    {
        $contentType = 'application/json';
        $method = 'POST';
        $url = 'https://api.commerce.naver.com/external/v1/pay-order/seller/product-orders/last-changed-statuses';
        $dispatchDate = $this->getDispatchDate();
        $data = [
            // $apiToken => apiToken,
            $productOrderId,
            'deliveryMethod' => 'DELIVERY',
            $deliveryCompany,
            $trackingNumber,
            $dispatchDate
        ];
        return $this->ssac->builder($account, $contentType, $method, $url, $data);
    }
    private function create($orderId, $deliveryCompany, $trackingNumber)
    {
        try {
            $deliveryCompanyId = DB::table('delivery_companies')
                ->where('smart_store', $deliveryCompany)
                ->value('id');

            DB::table('orders')
                ->where('id', $orderId)
                ->update([
                    'tracking_number' => $trackingNumber,
                    'delivery_company_id' => $deliveryCompanyId,
                    'updated_at' => now()
                ]);
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
        return [
            'status' => 'success',
            'message' => '송장번호 입력에 성공하였습니다',
            'data' => []
        ];
    }


    private function getAccount($id)
    {
        return DB::table('smart_store_accounts')->where('partner_id', $id)->first();
    }
    private function getDispatchDate()
    {
        $dateTime = new DateTime('now', new DateTimeZone('Asia/Seoul'));
        $dateTime->modify('+3 days'); // 현재 날짜로부터 3일 뒤로 설정
        $dispatchDate = $dateTime->format('Y-m-d\TH:i:s.vP');
        return $dispatchDate;
    }
}
