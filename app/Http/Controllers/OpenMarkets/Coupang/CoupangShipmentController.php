<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoupangShipmentController extends Controller
{
    private $ssac;
    public function __construct()
    {
        $this->ssac = new ApiController();
    }
    public function index(Request $request)
    {
        try {
            $trackingNumber = $request->input('trackingNumber');
            $deliveryCompanyId = $request->input('deliveryCompanyId');
            $productOrderNumber = $request->input('productOrderNumber');

            $deliveryCompany = $this->getDeliveryCompany($deliveryCompanyId);
            $order = $this->getOrder($productOrderNumber);
            $partner = $this->getPartnerByWingTransactionId($order->wing_transaction_id);
            $account = $this->getAccount($partner->id);
            $productOrder = $this->getProductOrder($order->id);
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '데이터 조회 중 오류가 발생했습니다: ' . $e->getMessage(),
            ];
        }
        try {
            return $this->getSingleOrder($account, $productOrder->product_order_number); //발주서 단건 조회
            $singleOrder = $this->getSingleOrder($account, $productOrder->product_order_number); //발주서 단건 조회

            $setProduct = $this->setProductAsPreparing($account, $productOrder->product_order_number); //상품준비중처리
            if (!$setProduct['data']['responseList'][0]['resultCode']) {
                return [
                    'status' => false,
                    'message' => '상품준비중으로 처리중 오류가 발생하였습니다.',
                    'data' => $setProduct
                ];
            }
            // setProduct를 하면 묶음배송번호가 변경됨으로 이거를 이용해서 송장번호 입력해야함
            $shipmentBoxId = $setProduct['data']['responseList'][0]['shipmentBoxId']; //분리배송임으로 0, 데이터가 하나밖에 없어서
            $orderId = $singleOrder['data']['orderId'];
            $vendorItemId = $singleOrder['data']['orderItems']['vendorItemId'];
            $preSplitShipped = DB::table('orders')
                ->where('orderId', $orderId)
                ->where('delivery_status', 'COMPLETE')
                ->exists();
            //singleOrderd의 orderId와 setProductd의 shipmentBoxId를 사용해서 postApi사용

            $responseApi = $this->postApi($account, $shipmentBoxId, $orderId, $deliveryCompany->coupang, $trackingNumber, $vendorItemId, $preSplitShipped);
            return $responseApi;
            if ($responseApi['response'] == false) {
                return [
                    'status' => false,
                    'message' => $responseApi['message'],
                ];
            }
            return $this->update($order->id, $deliveryCompanyId, $trackingNumber);
        } catch (\Exception $e) {
            return [
                'status' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    private function getSingleOrder($account, $productOrderNumber) //발주서 단건 조회
    {
        $method = 'GET';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/ordersheets' . '/' .  $productOrderNumber;
        return $this->ssac->build($method, $path, $account->access_key, $account->secret_key, $params = "");
    }
    private function setProductAsPreparing($account, $productOrderNumber) //상품준비중처리
    {
        $accessKey = $account->access_key;
        $secretKey = $account->secret_key;
        $contentType = 'application/json;charset=UTF-8';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/ordersheets/acknowledgement';
        $data = [
            'shipmentBoxIds' => [$productOrderNumber] //배열처리
        ];
        return $this->ssac->putBuilder($accessKey, $secretKey, $contentType, $path, $data);
    }
    private function postApi($account, $shipmentBoxId, $orderId, $deliveryCompanyCode, $invoiceNumber, $vendorItemId, $preSplitShipped)
    {
        $method = 'POST';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/orders/invoices';
        $params = [
            'vendorId' => $account->code,
            'orderSheetInvoiceApplyDtos' => [
                'shipmentBoxId' => $shipmentBoxId,
                'orderId' => $orderId,
                'deliveryCompanyCode' => $deliveryCompanyCode, //택배사
                'invoiceNumber' => $invoiceNumber, //송장번호
                'vendorItemId' => $vendorItemId, //data - orderItems - vendorItemId
                'splitShipping' => true,
                'preSplitShipped' => $preSplitShipped, //처음 orderId의 처음 분리배송이면 false, 나머지는 true
                'estimatedShippingDate' => ''
            ]
        ];
        return $this->ssac->build($method, $path, $account->accessKey, $account->secretKey, $params);
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

    private function getPartnerByWingTransactionId($wingTransactionId)
    {
        return DB::table('wing_transactions as wt')
            ->join('partner_domewing_accounts as pda', 'wt.member_id', '=', 'pda.domewing_account_id')
            ->join('partners as p', 'pda.partner_id', '=', 'p.id')
            ->where('wt.id', $wingTransactionId)
            ->where('pda.is_active', 'Y')
            ->select('p.*')
            ->first();
    }


    private function getProductOrder($orderId)
    {
        return DB::table('partner_orders as ps')
            ->where('ps.order_id', $orderId)
            ->first();
    }

    private function getAccount($id)
    {
        return DB::table('coupang_accounts')
            ->where('partner_id', $id)
            ->first();
    }
}
