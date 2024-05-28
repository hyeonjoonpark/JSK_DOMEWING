<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangCancelController;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangOrderController;
use App\Http\Controllers\SmartStore\SmartStoreCancelController;
use App\Http\Controllers\SmartStore\SmartStoreOrderController;
use App\Http\Controllers\WingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OpenMarketOrderController extends Controller
{
    public function index()
    {
        $allPartners = $this->getAllPartners(); //모든 파트너 조회
        $allOpenMarkets = $this->getAllOpenMarkets(); // 활성화중인 오픈마켓 조회
        $needSeedList = []; // 보유머니 부족한 사람들 리스트 보여주기
        foreach ($allPartners as $partner) { //모든 파트너 반복문
            $partnerDomewingAccount = $this->getPartnerDomewingAccount($partner->id); // 반복분 해당 파트너 조회
            $memberId = $partnerDomewingAccount->domewing_account_id;
            foreach ($allOpenMarkets as $openMarket) { // 오픈마켓 반복문
                $openMarketEngName = $openMarket->name_eng; //해당 오픈마켓 영어이름 구하기
                $isExistAccount = 'isExist'  . ucfirst($openMarketEngName) . 'Account';
                $isExistOpenMarketAccount = call_user_func([$this, $isExistAccount], $partner->id); //해당 파트너가 해당 오픈마켓 아이디가 있는지 없으면 패스
                if (!$isExistOpenMarketAccount) continue;
                $methodName = 'call' . ucfirst($openMarketEngName) . 'OrderApi'; //오픈마켓별 주문내역조회 api 쏠려고 영어이름을 메소드명으로 지정
                $uploadedProductMethod = 'get' . ucfirst($openMarketEngName) . 'UploadedProductId';  // 오픈마켓별 업로드된 상품인지 조회하려고 메소드명 지정
                $apiResults = call_user_func([$this, $methodName], $partner->id, $startDate = null, $endDate = null); //api 결과 저장
                if ($apiResults === false) continue; //api의 결과값이 비어있으면 continue 곧 아무런 결과값이 없는거지
                // orderId를 기준으로 그룹화
                $groupedResults = []; // wing_transaction에 주문의 총합으로 넣으려고 그룹화
                foreach ($apiResults as $apiResult) {
                    if (!is_array($apiResult)) {
                        continue;
                    }
                    if ($apiResult['productOrderStatus'] !== '결제완료' && $apiResult['productOrderStatus'] !== '상품준비중') {
                        continue; // 결제완료, 상품준비중 db에 저장하려고 검증
                    }
                    $orderId = $apiResult['orderId'];
                    if (!isset($groupedResults[$orderId])) {
                        $groupedResults[$orderId] = [];
                    }
                    $groupedResults[$orderId][] = $apiResult;
                }
                try {
                    DB::beginTransaction(); // 트랜잭션 시작
                    foreach ($groupedResults as $orderId => $orders) { //orderId기준 반복문 진행
                        // partner_orders 테이블에서 orders값 전체 중복 여부 확인
                        $orderNumbers = array_column($orders, 'orderId');
                        $productOrderNumbers = array_column($orders, 'productOrderId');
                        $exists = DB::table('partner_orders')
                            ->whereIn('order_number', $orderNumbers)
                            ->whereIn('product_order_number', $productOrderNumbers)
                            ->exists();
                        if ($exists) {
                            continue; // 저장 로직 건너뛰기
                        }
                        return $orders;
                        $totalPrice = 0;
                        $cartIds = [];
                        foreach ($orders as $order) {
                            $product = $this->getProduct($order['productCode']);
                            if (!$product) return [
                                'status' => false,
                                'message' => '도매윙에 등록된 상품이 아닙니다. 제품코드 : ' . $order['productCode']
                            ];
                            //1개의 주문에 대한 검증
                            $exist = DB::table('partner_orders')
                                ->where('order_number', $order['orderId'])
                                ->where('product_order_number', $order['productOrderId'])
                                ->exists();
                            if ($exist) {
                                continue; // 저장 로직 건너뛰기
                            }
                            $cart = $this->storeCart($memberId, $product->id, $order['quantity']);
                            $cartId = $cart['data']['cartId'];
                            $cartCode = $this->getCartCode($cartId);
                            $totalPrice += $this->getCartAmount($cartCode); // wing_transaction amount구하기
                            $cartIds[] = $cartId;  //cartId 리스트로 보관
                        }
                        $wingTransaction =  $this->storeWingTransaction($memberId, 'PAYMENT', $totalPrice, $remark = ''); //윙 트랜잭션 테이블 insert
                        $wingTransactionId = $wingTransaction['data']['wingTransactionId']; //저장한 데이터의 id값
                        foreach ($orders as $index => $order) {
                            //1개의 주문에 대한 검증
                            $exist = DB::table('partner_orders')
                                ->where('order_number', $order['orderId'])
                                ->where('product_order_number', $order['productOrderId'])
                                ->exists();
                            if ($exist) {
                                continue; // 저장 로직 건너뛰기
                            }
                            $product = $this->getProduct($order['productCode']);
                            $priceThen = $this->getSalePrice($product->id);
                            $orderResult = $this->storeOrder($wingTransactionId, $cartIds[$index], $order['receiverName'], $order['receiverPhone'], $order['address'], $order['remark'], $priceThen, $product->shipping_fee, $product->bundle_quantity);
                            $orderId = $orderResult['data']['orderId']; //order 테이블 insert하고 id값 챙기기
                            $uploadedProduct = call_user_func([$this, $uploadedProductMethod], $product->id); // 해당 오픈마켓 업로드테이블에서 업로드된 상품인지 확인하고 id값 가져옴
                            $uploadedProductId = $uploadedProduct->id;
                            $this->storePartnerOrder($orderId, $openMarket->id, $order['accountId'], $uploadedProductId, $priceThen, $product->shipping_fee, $order['orderId'], $order['productOrderId'], $order['accountId']); //partner_orders 테이블 insert
                        }
                    }
                    DB::commit(); // 트랜잭션 커밋
                } catch (\Exception $e) {
                    DB::rollBack(); // 트랜잭션 롤백
                    return [
                        'status' => false,
                        'message' => $e->getMessage(),
                        'error' => $e->getMessage(),
                    ];
                }
            }
        }
        $orders = $this->getPaidOrders();
        $processedOrders = $orders->map(function ($order) {
            return $this->processOrder($order);
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
        return [
            'status' => true,
            'message' => '성공적으로 오더윙을 가동하였습니다.',
            'data' => $results
        ];
    }
    public function cancelOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'remark' => 'required',
        ], [
            'remark.required' => '취소 사유를 입력해야합니다.'
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => '취소 사유를 입력해야합니다.',
                'error' => $validator->errors(),
            ];
        }
        $productOrderNumber = $request->productOrderNumber;
        $order = DB::table('orders as o') //주문내역가지고 작업하기전에 유효한지 확인하고 없으면 return
            ->where('product_order_number', $productOrderNumber)
            ->where('delivery_status', 'PENDING')
            ->where('type', 'PAID')
            ->first();
        if (!$order) return [
            'status' => false,
            'message' => '이미 취소되었거나 유효한 주문이 아닙니다.',
        ];
        $remark = $request->remark; //취소 요청 reason
        $partnerOrder = DB::table('partner_orders as po') //해당 주문의 partner_order 테이블 조회
            ->where('order_id', $order->id)
            ->first();
        $vendor = DB::table('vendors as v') //해당 주문의 오픈마켓이 어디인지 조회
            ->where('v.id', $partnerOrder->vendor_id)
            ->where('is_active', 'ACTIVE')
            ->first();
        if ($vendor) {
            $vendorEngName = $vendor->name_eng;
            $method = 'call' . ucfirst($vendorEngName) . 'CancelApi'; //해당 오픈마켓의 api 호출을 위한 메소드 작성
            $apiResult = call_user_func([$this, $method], $productOrderNumber); //api 결과 저장
            if (!$apiResult['status']) return $apiResult;
        }

        DB::beginTransaction();
        try {
            // orders 테이블 업데이트
            DB::table('orders')
                ->where('product_order_number', $productOrderNumber)
                ->where('delivery_status', 'PENDING')
                ->update([
                    'type' => 'CANCELLED',
                    'remark' => $remark
                ]);
            // 트랜잭션 커밋
            DB::commit();
            return [
                'status' => true,
                'message' => '주문이 성공적으로 취소되었습니다.',
            ];
        } catch (\Exception $e) {
            // 트랜잭션 롤백
            DB::rollBack();
            return [
                'status' => false,
                'message' => '주문 취소 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ];
        }
    }
    private function getPartnerDomewingAccount($partnerId)
    {
        return DB::table('partner_domewing_accounts')
            ->where('partner_id', $partnerId)
            ->where('is_active', 'Y')
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
                    'delivery_status' => 'PENDING',
                    'type' => 'PAID',
                    'price_then' => $priceThen,
                    'shipping_fee_then' => $shippingFeeThen,
                    'bundle_quantity_then' => $bundleQuantityThen
                ]);
            return [
                'status' => true,
                'data' => [
                    'orderId' => $orderId
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


    private function storePartnerOrder($orderId, $vendorId, $accountId, $uploadedProductId, $price, $shippingFee, $orderNumber, $productOrderNumber)
    {
        try {
            DB::table('partner_orders')
                ->insertGetId([
                    'order_id' => $orderId,
                    'vendor_id' => $vendorId,
                    'account_id' => $accountId,
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
    private function getCartCode($cartId)
    {
        return DB::table('carts')
            ->where('id', $cartId)
            ->value('code');
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
                'o.receiver_remark as receiverRemark',
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
                ->first();

            if ($uploadedProduct) {
                $product = DB::table('minewing_products as mp')
                    ->where('mp.id', $uploadedProduct->product_id)
                    ->first();
            }
        } else {
            $product = DB::table('minewing_products as mp')
                ->where('mp.id', $order->productId)
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
            'isPartner' => $order->isExist,
            'isActive' => $product->isActive,
            'receiverRemark' => $order->receiverRemark
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
    private function callSmart_storeCancelApi($productOrderNumber)
    {
        $controller = new SmartStoreCancelController();
        return $controller->index($productOrderNumber);
    }
    private function callCoupangCancelApi($productOrderNumber)
    {
        $controller = new CoupangCancelController();
        return $controller->index($productOrderNumber);
    }

    private function getSmart_storeUploadedProductId($productId)
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
    private function isExistSmart_storeAccount($partnerId)
    {
        return DB::table('smart_store_accounts')
            ->where('partner_id', $partnerId)
            ->where('is_active', 'ACTIVE')
            ->exists();
    }
    private function isExistCoupangAccount($partnerId)
    {
        return DB::table('coupang_accounts')
            ->where('partner_id', $partnerId)
            ->where('is_active', 'ACTIVE')
            ->exists();
    }
}
