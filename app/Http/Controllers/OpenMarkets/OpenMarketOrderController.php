<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangOrderController;
use App\Http\Controllers\SmartStore\SmartStoreOrderController;
use App\Http\Controllers\WingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class OpenMarketOrderController extends Controller
{
    public function index()
    {
        // $allPartners = $this->getAllPartners();
        // $allOpenMarkets = $this->getAllOpenMarkets();
        // foreach ($allPartners as $partner) {
        //     $memberId = $this->getMemberId($partner->id);
        //     foreach ($allOpenMarkets as $openMarket) {
        //         $openMarketEngName = $openMarket->name_eng;
        //         $methodName = 'call' . ucfirst($openMarketEngName) . 'OrderApi';
        //         $uploadedProductMethod = 'get' . ucfirst($openMarketEngName) . 'UploadedProductId';
        //         $apiResults = call_user_func([$this, $methodName], $partner->id, $startDate = null, $endDate = null);
        //         if ($apiResults === false || $apiResults == []) continue;
        //         // orderId를 기준으로 그룹화
        //         $groupedResults = [];
        //         foreach ($apiResults as $apiResult) {
        //             if ($apiResult->productOrderStatus !== '결제완료') continue;
        //             $orderId = $apiResult->orderId;
        //             if (!isset($groupedResults[$orderId])) {
        //                 $groupedResults[$orderId] = [];
        //             }
        //             $groupedResults[$orderId][] = $apiResult;
        //         }

        //         foreach ($groupedResults as $orderId => $orders) {
        //             $amount = array_reduce($orders, function ($carry, $order) {
        //                 $productId = $this->getProduct($order->productCode)->id;
        //                 $productSale = $this->getSalePrice($productId);
        //                 return $carry + ($productSale * $order->quantity);
        //             }, 0);
        //             $wingTransaction = $this->storeWingTransaction($memberId, 'PAYMENT', $amount, $remark = '');
        //             $wingTransactionId = $wingTransaction['data']['wingTransactionId'];

        //             foreach ($orders as $order) {
        //                 $product = $this->getProduct($order->productCode);
        //                 $cartResult = $this->storeCart($memberId, $product->id, $order->quantity);
        //                 $priceThen = $this->getSalePrice($product->id);
        //                 $cartId = $cartResult['data']['cartId'];
        //                 $orderResult = $this->storeOrder($wingTransactionId, $cartId, $order->receiverName, $order->receiverPhone, $order->address, $order->Remark, $priceThen, $product->shipping_fee, $product->bundle_quantity);
        //                 $orderId = $orderResult['data']['orderId'];
        //                 $uploadedProductId = call_user_func([$this, $uploadedProductMethod], $product->id);
        //                 $this->storePartnerOrder($orderId, $openMarket->id, $uploadedProductId, $priceThen, $product->shipping_fee, $order->orderId, $order->productOrderId);
        //             }
        //         }
        //     }
        // }
        $orders = $this->getPaidOrders();
        $processedOrders = $orders->map(function ($order) {
            return $this->processOrder($order);
        });
        return response()->json($processedOrders);
    }
    private function getMemberId($partnerId)
    {
        return DB::table('partner_domewing_accounts')
            ->where('partner_id', $partnerId)
            ->where('is_active', 'Y')
            ->select('domewing_account_id')
            ->first();
    }
    private function storeOrder($wingTransactionId, $cartId, $receiverName, $receiverPhone, $receiverAddress, $receiverRemark, $priceThen, $shippingFeeThen, $bundleQuantityThen)
    {
        try {
            $orderId = DB::table('orders')
                ->insertGetId([
                    'wing_transaction_id' => $wingTransactionId,
                    'cart_id' => $cartId,
                    'product_order_number' => (string) Str::uuid(),
                    'receiver_name' => $receiverName,
                    'receiver_phone' => $receiverPhone,
                    'receiver_address' => $receiverAddress,
                    'receiver_remark' => $receiverRemark,
                    'status' => 'APPROVED',
                    'type' => 'PAID',
                    'price_then' => $priceThen,
                    'shipping_fee_then' => $shippingFeeThen,
                    'bundle_quantity_then' => $bundleQuantityThen
                ]);
            return [
                'status' => true,
                'data' => $orderId
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '주문 내역을 저장하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }

    private function storePartnerOrder($orderId, $vendorId, $uploadedProductId, $price, $shippingFee, $orderNumber, $productOrderNumber)
    {
        try {
            DB::table('partner_orders')
                ->insertGetId([
                    'order_id' => $orderId,
                    'vendor_id' => $vendorId,
                    'uploaded_product_id' => $uploadedProductId,
                    'price_then' => $price,
                    'shipping_fee_then' => $shippingFee,
                    'order_number' => $orderNumber,
                    'product_order_number' => $productOrderNumber,
                ]);
            return [
                'status' => true
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '주문 내역을 저장하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }

    // 카트 금액 계산
    private function getCartAmount($cartCode)
    {
        $cart = DB::table('carts AS c')
            ->join('minewing_products AS mp', 'mp.id', '=', 'c.product_id')
            ->where('c.code', $cartCode)
            ->first();

        $salePrice = $this->getSalePrice($cart->product_id);
        $shippingRate = $cart->bundle_quantity === 0 ? 1 : ceil($cart->quantity / $cart->bundle_quantity);
        return $salePrice * $cart->quantity + $cart->shipping_fee * $shippingRate;
    }
    // 판매 가격 계산
    private function getSalePrice($productId)
    {
        $originProductPrice = DB::table('minewing_products')
            ->where('id', $productId)
            ->value('productPrice');

        $promotion = DB::table('promotion_products AS pp')
            ->join('promotion AS p', 'p.id', '=', 'pp.promotion_id')
            ->where('product_id', $productId)
            ->where('p.end_at', '>', now())
            ->where('p.is_active', 'Y')
            ->where('pp.is_active', 'Y')
            ->where('p.band_promotion', 'N')
            ->where('pp.band_product', 'N')
            ->value('pp.product_price');

        $productPrice = $promotion ?? $originProductPrice;

        $margin = DB::table('sellwing_config')->where('id', 1)->value('value');
        $marginRate = ($margin / 100) + 1;

        return ceil($productPrice * $marginRate);
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
        return [
            'status' => true,
            'message' => '성공적으로 오더윙을 가동하였습니다.',
            'data' => $results
        ];
    }
    private function getPaidOrders()
    {
        return DB::table('orders as o')
            ->leftJoin('partner_orders as po', 'o.id', '=', 'po.order_id')
            ->leftJoin('vendors as v', 'v.id', '=', 'po.vendor_id')
            ->join('wing_transactions as wt', 'wt.id', '=', 'o.wing_transaction_id')
            ->join('members as m', 'm.id', '=', 'wt.member_id')
            ->join('carts as c', 'c.id', '=', 'o.cart_id')
            ->where('o.type', 'PAID')
            ->where('wt.status', 'APPROVED')
            ->where('o.delivery_status', 'PENDING')
            ->select(
                'm.username as member_username',
                'c.quantity as quantity',
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
                'po.order_number as orderNumber',
                'o.product_order_number as productOrderNumber',
                'm.id as memberId',
                'c.product_id as productId',
                DB::raw('IF(po.order_id IS NOT NULL, true, false) as isExist')
            )
            ->get();
    }
    private function processOrder($order)
    {
        $product = null;
        if ($order->vendor_name_eng && $order->uploadedProductId) {
            $uploadedProductsTable = $order->vendor_name_eng . '_uploaded_products';
            $uploadedProduct = DB::table($uploadedProductsTable)
                ->where('id', $order->uploadedProductId)
                ->where('is_active', 'Y')
                ->first();

            if ($uploadedProduct) {
                $product = DB::table('minewing_products as mp')
                    ->where('mp.id', $uploadedProduct->product_id)
                    ->where('isActive', 'Y')
                    ->first();
            }
        } else {
            $product = DB::table('minewing_products as mp')
                ->where('mp.id', $order->productId)
                ->where('isActive', 'Y')
                ->first();
        }

        $deliveryCompanies = $this->getDeliveryCompanies();

        return [
            'userName' => $order->member_username,
            'orderNumber' => $order->orderNumber,
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
            'deliveryCompanies' => $deliveryCompanies,
            'productOrderNumber' => $order->productOrderNumber,
            'isPartner' => $order->isExist
        ];
    }
    private function getAllPartners()
    {
        return DB::table('partners as p')
            ->join('partner_domewing_accounts as pda', 'p.id', '=', 'pda.partner_id')
            ->where('p.is_active', 'ACTIVE')
            ->where('pda.is_active', 'Y')
            ->select('p.*')
            ->get();
    }
    private function getAllOpenMarkets()
    {
        return DB::table('vendors')
            ->where('is_active', 'ACTIVE')
            ->where('type', 'OPEN_MARKET')
            ->get();
    }
    private function calcProductPrice($productPrice)
    {
        $config = DB::table('sellwing_config')->first();
        $marginRate = $config->value;
        $processMarginRate = 1 + ($marginRate) / 100;
        return $productPrice * $processMarginRate;
    }

    private function getDeliveryCompanies()
    {
        $columns = Schema::getColumnListing('delivery_companies');
        $query = DB::table('delivery_companies');
        foreach ($columns as $column) {
            $query->whereNotNull($column);
        }
        return $query->get();
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

    private function getPartner($id)
    {
        return DB::table('partners')
            ->where('id', $id)
            ->first();
    }
    private function getDomewingUser($id)
    {
        return DB::table('members')
            ->where('id', $id)
            ->first();
    }
    // 카트 테이블 저장
    private function storeCart($memberId, $productId, $quantity)
    {
        try {
            $cartId = DB::table('carts')
                ->insertGetId([
                    'member_id' => $memberId,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'code' => (string) Str::uuid(),
                    'status' => 'PAID'
                ]);

            return [
                'status' => true,
                'data' => [
                    'cartId' => $cartId
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '신규 주문을 저장하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    // Wing 거래 저장
    private function storeWingTransaction($memberId, $type, $amount, $remark = null)
    {
        try {
            $wingTransactionId = DB::table('wing_transactions')
                ->insertGetId([
                    'member_id' => $memberId,
                    'order_number' => (string) Str::uuid(),
                    'type' => $type,
                    'amount' => $amount,
                    'remark' => $remark
                ]);

            return [
                'status' => true,
                'data' => [
                    'wingTransactionId' => $wingTransactionId
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '주문 내역을 저장하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    private function getProduct($productCode)
    {
        return DB::table('minewing_products')
            ->where('productCode', $productCode)
            ->where('isActive', 'Y')
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
    private function getSmart_stroeUploadedProductId($productId)
    {
        return DB::table('smart_store_uploaded_products')
            ->where('product_id', $productId)
            ->where('is_active', 'Y')
            ->select('id')
            ->first();
    }
    private function getCoupangUploadedProductId($productId)
    {
        return DB::table('coupang_uploaded_products')
            ->where('product_id', $productId)
            ->where('is_active', 'Y')
            ->select('id')
            ->first();
    }
}
