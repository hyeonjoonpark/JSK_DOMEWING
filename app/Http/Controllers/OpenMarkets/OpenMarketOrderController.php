<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangOrderController;
use App\Http\Controllers\SmartStore\SmartStoreOrderController;
use App\Http\Controllers\WingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OpenMarketOrderController extends Controller
{
    public function index()
    {
        $orders = DB::table('partner_orders as po')
            ->join('orders as o', 'po.order_id', '=', 'o.id')
            ->join('vendors as v', 'v.id', '=', 'po.vendor_id')
            ->join('wing_transactions as wt', 'wt.id', '=', 'o.wing_transaction_id')
            ->join('members as m', 'm.id', '=', 'wt.member_id')
            ->join('carts as c', 'c.id', '=', 'o.cart_id')
            ->where('o.type', 'PAID')
            ->where('o.status', 'APPROVED')
            ->where('o.delivery_status', 'PENDING')
            ->select(
                'm.username as member_username',
                'po.price_then as price_then',
                'c.quantity as quantity',
                'po.shipping_fee_then as shipping_fee_then',
                'po.order_number as order_number',
                'po.product_order_number as product_order_number',
                'po.vendor_id as vendor_id',
                'o.receiver_name as receiver_name',
                'o.receiver_phone as receiver_phone',
                'o.receiver_address as receiver_address',
                'v.name as vendor_name',
                'v.name_eng as vendor_name_eng',
                'po.uploaded_product_id as uploadedProductId',
                'o.id as order_id',
                'm.username as senderNickName',
                'm.phone_number as senderPhone',
                'm.email as senderEmail',
                'm.last_name as lastName',
                'm.first_name as firstName',

            )
            ->get();
        $processedOrders = $orders->map(function ($order) {
            $uploadedProductsTable = $order->vendor_name_eng . '_uploaded_products';
            $uploadedProduct = DB::table($uploadedProductsTable)
                ->where('id', $order->uploadedProductId)
                ->where('is_active', 'Y')
                ->first();
            $product = DB::table('minewing_products as mp')
                ->where('mp.id', $uploadedProduct->product_id)
                ->where('isActive', 'Y')
                ->first();

            return [
                'userName' => $order->member_username,
                'orderNumber' => $order->order_number,
                'productOrderNumber' => $order->product_order_number,
                'receiverName' => $order->receiver_name,
                'receiverPhone' => $order->receiver_phone,
                'receiverAddress' => $order->receiver_address,
                'vendorName' => $order->vendor_name,
                'vendorNameEng' => $order->vendor_name_eng,
                'productName' => $product ? $product->productName : null,
                'orderId' => $order->order_id,
                'productHref' => $product ? $product->productHref : null,
                'productImage' => $product ? $product->productImage : null,
                'productPrice' => $product ? $this->calcProductPrice($product->productPrice) : null,
                'shippingFee' => $product ? $product->shipping_fee : null,
                'quantity' => $order->quantity,
                'amount' => $product ? $this->calcProductPrice($product->productPrice) * $order->quantity + $product->shipping_fee : null,
                'orderStatus' => '신규주문',
                'senderNickName' => $order->senderNickName,
                'senderPhone' => $order->senderPhone,
                'senderEmail' => $order->senderEmail,
                'senderName' => $order->lastName . $order->firstName,
            ];
        });
        return response()->json($processedOrders);
    }
    public function indexPartner(Request $request)
    {
        $apiToken = $request->apiToken;
        $currentPartnerId = DB::table('partners')
            ->where('api_token', $apiToken)
            ->value('id');
        $marketIds = $request->input('openMarketIds', []);
        $orderwingEngNameLists = $this->getOrderwingOpenMarkets($marketIds);
        $domewingAndPartner = $this->getDomewingAndPartners($currentPartnerId);
        if (!$domewingAndPartner) {
            return [
                'status' => false,
                'message' => '도매윙 계정연동을 해야합니다.',
                'data' => []
            ];
        }
        $wc = new WingController();
        $wing = $wc->getBalance($domewingAndPartner->domewing_account_id);
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $results = [];
        $saveResults = [];
        $totalAmountRequired = 0;
        $partner = $this->getPartner($domewingAndPartner->partner_id);
        $domewingUser = $this->getDomewingUser($domewingAndPartner->domewing_account_id);
        foreach ($orderwingEngNameLists as $orderwingEngNameList) {
            $methodName = 'call' . ucfirst($orderwingEngNameList) . 'OrderApi';
            if (method_exists($this, $methodName)) {
                $apiResult = call_user_func([$this, $methodName], $partner->id, $startDate, $endDate);
                if (isset($apiResult) && is_array($apiResult)) {
                    foreach ($apiResult as $order) {
                        if ($order['productOrderStatus'] == "결제완료") {
                            $totalAmountRequired += $order['totalPaymentAmount'] + $order['deliveryFeeAmount'];
                            $saveResults[] = $order;
                        }
                    }
                }
            } else {
                $apiResult = null;
                Log::error("Method $methodName does not exist.");
            }
            $results[] = [
                'domewing_user_name' => $domewingUser->username,
                'api_result' => $apiResult
            ];
        }
        if ($totalAmountRequired > $wing) {
            return [
                'status' => false,
                'message' => 'wing 잔액이 부족합니다.',
                'data' => $totalAmountRequired - $wing,
            ];
        }
        // foreach ($saveResults as $result) {
        //     if ($result['productOrderStatus'] == "결제완료") {


        //         $isExistOrder = DB::table('transaction_wing')
        //             ->where('product_order_id', $result['productOrderId'])
        //             ->exists();

        //         if ($isExistOrder) continue;

        //         $finishSave = $wc->saveOrder($result, $domewingAndPartner, $domewingUser, $totalAmountRequired);
        //         if (!$finishSave['status']) {  // 배열 접근 방식을 사용
        //             return [
        //                 'status' => false,
        //                 'message' => $finishSave['message']  // 메시지도 배열에서 추출
        //             ];
        //         }
        //     }
        // }
        return [
            'status' => true,
            'message' => '성공적으로 오더윙을 가동하였습니다.',
            'data' => $results
        ];
    }
    private function calcProductPrice($productPrice)
    {
        $config = DB::table('sellwing_config')->first();
        $marginRate = $config->value;
        $processMarginRate = 1 + ($marginRate) / 100;
        return $productPrice * $processMarginRate;
    }

    private function getOrderwingOpenMarkets($marketIds)
    {
        $result = DB::table('vendors AS v')
            ->whereIn('v.id', $marketIds)
            ->where('v.is_active', 'active')
            ->select('name_eng')
            ->get();

        return $result->pluck('name_eng')->toArray();
    }
    private function getDomewingAndPartners($partnerId = null) //그리고 어떤 도매윙 아이디의 오픈마켓 정보들인가를 가져와
    {
        $query = DB::table('partner_domewing_accounts as da')
            ->where('is_active', 'Y')
            ->select('partner_id', 'domewing_account_id');
        if ($partnerId) {
            return $query->where('partner_id', $partnerId)->first();
        }
        return $query->get();
    }

    private function getPartner($id) // 이걸로 파트너를 가져와서 그 파트너의 오픈마켓들을 싹 돌릴거야
    {
        return DB::table('partners')
            ->where('id', $id)
            ->first();
    }
    private function getDomewingUser($id) //도매윙 계정을 가져와? 왜? 이름 줄려고
    {
        return DB::table('members')
            ->where('id', $id)
            ->first();
    }

    private function callSmart_storeOrderApi($id, $startDate, $endDate)
    {
        $controller = new SmartStoreOrderController();
        return $controller->index($id, $startDate, $endDate);
    }
    private function callCoupangOrderApi($id, $startDate, $endDate)
    {
        $controller = new CoupangOrderController();
        return $controller->index($id, $startDate, $endDate);
    }


    private function callSmart_storeShipmentApi()
    {
        $controller = new SmartStoreOrderController();
    }
    private function callCoupangShipmentApi()
    {
        $controller = new CoupangOrderController();
    }
}
