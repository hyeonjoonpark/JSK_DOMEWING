<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangCancelController;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangOrderController;
use App\Http\Controllers\OpenMarkets\St11\St11OrderController;
use App\Http\Controllers\SmartStore\SmartStoreCancelController;
use App\Http\Controllers\SmartStore\SmartStoreOrderController;
use App\Http\Controllers\WingController;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OpenMarketOrderController extends Controller
{
    public function index()
    {
        $allPartners = $this->getAllPartners(); //모든 파트너 조회
        $allOpenMarkets = $this->getAllOpenMarkets(); // 활성화중인 오픈마켓 조회
        foreach ($allPartners as $partner) { //모든 파트너 반복문
            $memberId = $partner->domewing_account_id;
            foreach ($allOpenMarkets as $openMarket) { // 오픈마켓 반복문
                $openMarketEngName = $openMarket->name_eng; //해당 오픈마켓 영어이름 구하기
                // Charles: 현재 계정이 존재하는지 DB 조회 1번, 나중에 계정 정보 불러올 때 1번. 총 2번의 DB 호출이 발생하고 있음.
                // Charles: first() 메소드를 이용한다면, 계정이 없을시 null 을 리턴함. 이 부분을 활용하면 한 번의 DB 호출로
                // Charles: 계정이 없는지/있으면 계정 정보를 가져오는 처리를 한 번에 수행할 수 있음.
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
                    if (!$apiResult || !is_array($apiResult) || !isset($apiResult['orderId'])) {
                        continue;
                    }
                    if ($apiResult['productOrderStatus'] !== '결제완료' && $apiResult['productOrderStatus'] !== '상품준비중') {
                        continue; // 결제완료, 상품준비중 db에 저장하려고 검증
                    } //일부러 결제완료, 상품준비중을 먼저 검증 왜냐 db조회보다 간단한 배열 조회가 더빠르기 때문에 성능 최적화
                    //주문이 이미 있는지 검증
                    $isExist = DB::table('partner_orders as po')
                        ->join('orders as o', 'o.id', '=', 'po.order_id')
                        ->where('po.order_number', $apiResult['orderId'])
                        ->where('po.product_order_number', $apiResult['productOrderId'])
                        ->whereIn('o.type', ['PAID', 'CANCELLED'])
                        ->exists();
                    if ($isExist) {
                        continue; // 저장 로직 건너뛰기
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
                        $totalPrice = 0;
                        $cartIds = [];
                        foreach ($orders as $order) {
                            $product = $this->getProduct($order['productCode']);
                            if (!$product) continue; //셀러가 우리 제품 말고 다른걸 올릴 수 있음
                            $cart = $this->storeCart($memberId, $product->id, $order['quantity']);
                            $cartId = $cart['data']['cartId'];
                            $cartCode = $this->getCartCode($cartId);
                            $totalPrice += $this->getCartAmount($cartCode); // wing_transaction amount구하기
                            $cartIds[] = $cartId;  //cartId 리스트로 보관
                        }
                        if ($totalPrice === 0) continue;
                        $wingTransaction =  $this->storeWingTransaction($memberId, 'PAYMENT', $totalPrice, $remark = ''); //윙 트랜잭션 테이블 insert
                        $wingTransactionId = $wingTransaction['data']['wingTransactionId']; //저장한 데이터의 id값
                        foreach ($orders as $index => $order) {
                            $product = $this->getProduct($order['productCode']);
                            if (!$product) continue;
                            $priceThen = $this->getSalePrice($product->id);
                            $orderResult = $this->storeOrder($wingTransactionId, $cartIds[$index], $order['receiverName'], $order['receiverPhone'], $order['address'], $order['remark'], $priceThen, $product->shipping_fee, $product->bundle_quantity, $order['orderDate']);
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
                        'message' => '새로운 주문 저장과정에서 오류가 발생하였습니다.',
                        'error' => $e->getMessage(),
                    ];
                }
            }
        }
        return [
            'status' => true,
            'message' => '새로운 주문 저장이 완료되었습니다.'
        ];
    }
    public function showData(Request $request)
    {
        set_time_limit(180);
        try {
            $validator = Validator::make($request->all(), [
                'vendors' => 'required|array',
                'vendors.*' => 'exists:vendors,id',
                'orderStatus' => 'required|string'
            ], [
                'vendors.required' => '하나 이상의 원청사를 선택해야합니다.',
                'vendors.array' => '원청사 선택 형식이 잘못되었습니다.',
                'vendors.*.exists' => '선택한 원청사가 존재하지 않습니다.',
                'orderStatus.required' => '주문 상태를 선택해야합니다.',
                'orderStatus.string' => '주문 상태 형식이 잘못되었습니다.'
            ]);
            if ($validator->fails()) {
                return [
                    'status' => false,
                    'message' => $validator->errors()->first(),
                    'error' => $validator->errors(),
                ];
            }
            $vendors = $request->input('vendors');
            $orderStatus = $request->input('orderStatus');
            $orders = $this->getOrders($vendors, $orderStatus);
            $processedOrders = array_map(function ($order) {
                return $this->transformOrderDetails($order);
            }, $orders);
            $lowBalanceAccounts = $this->getUsersWithLowBalance();
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '내역을 불러오는 도중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ];
        }
        return response()->json([
            'lowBalanceAccounts' => $lowBalanceAccounts,
            'processedOrders' => $processedOrders,
        ]);
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
        // if ($totalAmountRequired > $wing) {
        //     return [
        //         'status' => false,
        //         'message' => 'wing 잔액이 부족합니다.',
        //         'data' => $totalAmountRequired - $wing,
        //     ];
        // }
        return [
            'status' => true,
            'message' => '성공적으로 오더윙을 가동하였습니다.',
            'data' => $results
        ];
    }
    public function processOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productOrderNumber' => 'required|string',
            'targetStatus' => 'required|string',
            'trackingNumber' => 'nullable|string',
            'remark' => 'nullable|string',
        ], [
            'productOrderNumber.required' => '주문 번호는 필수 항목입니다.',
            'productOrderNumber.string' => '주문 번호는 문자열이어야 합니다.',
            'targetStatus.required' => '주문 상태를 선택해야 합니다.',
            'targetStatus.string' => '주문 상태 형식이 잘못되었습니다.',
            'trackingNumber.string' => '송장 번호는 문자열이어야 합니다.',
            'remark.string' => '사유는 문자열이어야 합니다.',
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first(),
                'error' => $validator->errors(),
            ];
        }
        $productOrderNumber = $request->productOrderNumber;
        $orderType = DB::table('orders')
            ->where('product_order_number', $productOrderNumber)
            ->value('type');
        $targetStatus = $request->targetStatus;
        $remark = $request->remark;
        if ($targetStatus == 'shipment-complete' && $orderType == 'EXCHANGE') {
            $exchangeController = new OpenMarketExchangeController();
            return $exchangeController->saveExchangeShipment($request);
        }
        if ($targetStatus == 'order-cancel' && $orderType == 'EXCHANGE') {
            $exchangeController = new OpenMarketExchangeController();
            return $exchangeController->cancelExchange($request);
        }
        if ($targetStatus == 'shipment-complete' && $orderType == 'REFUND') {
            $exchangeController = new OpenMarketRefundController();
            return $exchangeController->saveRefundShipment($request);
        }
        if ($targetStatus == 'order-cancel' && $orderType == 'REFUND') {
            $exchangeController = new OpenMarketRefundController();
            return $exchangeController->cancelRefund($request);
        }
        if ($targetStatus == 'awaiting-shipment') {
            $setAwaitingController = new OpenMarketSetAwaitingController();
            return $setAwaitingController->setAwaitingShipmentStatus($request->productOrderNumber);
        }
        if ($targetStatus == 'shipment-complete') {
            $shipmentController = new OpenMarketShipmentController();
            return $shipmentController->saveShipment($request);
        }
        if ($targetStatus == 'order-cancel') {
            return $this->cancelOrder($productOrderNumber, $remark);
        }
        if ($targetStatus == 'accept-cancel') {
            return $this->acceptCancel($productOrderNumber, $remark);
        }
    }
    public function cancelOrder($productOrderNumber, $remark)
    {
        if (!$remark) return [
            'status' => false,
            'message' => '취소사유는 필수입니다.',
        ];
        $order = DB::table('orders as o') //주문내역가지고 작업하기전에 유효한지 확인하고 없으면 return
            ->where('product_order_number', $productOrderNumber)
            ->where('delivery_status', 'PENDING')
            ->where('type', 'PAID')
            ->first();
        if (!$order) return [
            'status' => false,
            'message' => '이미 취소되었거나 유효한 주문이 아닙니다.',
        ];
        $partnerOrder = DB::table('partner_orders as po') //해당 주문의 partner_order 테이블 조회
            ->where('order_id', $order->id)
            ->first();
        if ($partnerOrder) {
            $vendor = DB::table('vendors as v') //해당 주문의 오픈마켓이 어디인지 조회
                ->where('v.id', $partnerOrder->vendor_id)
                ->where('is_active', 'ACTIVE')
                ->first();
            $vendorEngName = $vendor->name_eng;
            $method = 'call' . ucfirst($vendorEngName) . 'CancelApi'; //해당 오픈마켓의 api 호출을 위한 메소드 작성
            $apiResult = call_user_func([$this, $method], $productOrderNumber); //api 결과 저장
            if (!$apiResult['status']) return [
                'status' => false,
                'message' => '오픈마켓 주문취소 과정에서 오류가 발생하였습니다.',
                'data' => $apiResult
            ];
        }

        DB::beginTransaction();
        try {
            // orders 테이블 업데이트
            DB::table('orders')
                ->where('product_order_number', $productOrderNumber)
                ->where('delivery_status', 'PENDING')
                ->update([
                    'type' => 'CANCELLED',
                    'remark' => $remark,
                    'requested' => 'N'
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
    public function acceptCancel($productOrderNumber, $remark)
    {
        if (!$remark) return [
            'status' => false,
            'message' => '취소사유는 필수입니다.',
        ];
        $order = DB::table('orders as o') //주문내역가지고 작업하기전에 유효한지 확인하고 없으면 return
            ->where('product_order_number', $productOrderNumber)
            ->where('delivery_status', 'PENDING')
            ->where('type', 'PAID')
            ->first();
        if (!$order) return [
            'status' => false,
            'message' => '이미 취소되었거나 유효한 주문이 아닙니다.',
        ];
        DB::beginTransaction();
        try {
            // orders 테이블 업데이트
            DB::table('orders')
                ->where('product_order_number', $productOrderNumber)
                ->where('delivery_status', 'PENDING')
                ->update([
                    'type' => 'CANCELLED',
                    'remark' => $remark,
                    'requested' => 'N'
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
    public function setMemo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'adminRemark' => 'required|string'
        ], [
            'adminRemark.required' => '메모를 입력해야합니다.',
            'adminRemark.string' => '메모는 문자열입니다.',
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first(),
                'error' => $validator->errors(),
            ];
        }
        $productOrderNumber = $request->productOrderNumber;
        $adminRemark = $request->adminRemark;
        $update = DB::table('orders')
            ->where('product_order_number', $productOrderNumber)
            ->update(['admin_remark' => $adminRemark]);
        if (!$update) return [
            'status' => false,
            'message' => '해당 주문을 찾을 수 없습니다'
        ];
        return [
            'status' => true,
            'message' => '메모가 성공적으로 저장되었습니다.',
        ];
    }
    public function getOrderInfo(Request $request)
    {
        $productOrderNumber = $request->productOrderNumber;
        $order = DB::table('orders')
            ->where('product_order_number', $productOrderNumber)
            ->first();
        $orderDetails = DB::table('order_details')
            ->where('order_id', $order->id)
            ->first();
        $wingTransaction = DB::table('wing_transactions')
            ->where('id', $order->wing_transaction_id)
            ->first();
        $imageUrl = $orderDetails->image ? 'https://domewing.com/storage/assets/images/exchange-refund/' . $orderDetails->image : null;
        return response()->json([
            'name' => $order->receiver_name,
            'phone' => $order->receiver_phone,
            'address' => $order->receiver_address,
            'reason' => $orderDetails->reason,
            'type' => $orderDetails->type,
            'quantity' => $orderDetails->quantity,
            'image' => $imageUrl,
            'amount' => $wingTransaction->amount,
        ]);
    }
    private function getUsersWithLowBalance()
    {
        $lowBalanceAccounts = [];
        $accounts = DB::table('members')->get();
        $wc = new WingController();
        foreach ($accounts as $account) {
            $balance = $wc->getBalance($account->id);
            if ($balance < 0) {
                $name = $account->last_name . '' . $account->first_name;
                $lowBalanceAccounts[] = $name; // 배열에 값을 추가하는 방식으로 수정
            }
        }
        return $lowBalanceAccounts; // 결과를 반환
    }
    private function storeOrder($wingTransactionId, $cartId, $receiverName, $receiverPhone, $receiverAddress, $receiverRemark, $priceThen, $shippingFeeThen, $bundleQuantityThen, $orderDate = null)
    {
        try {
            $orderData = [
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
                'bundle_quantity_then' => $bundleQuantityThen,
            ];
            if ($orderDate !== null) {
                $orderData['created_at'] = $orderDate;
            }
            $orderId = DB::table('orders')->insertGetId($orderData);
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
    private function getOrders($vendors, $orderStatus)
    {
        $query = DB::table('orders as o')
            ->leftJoin('partner_orders as po', 'o.id', '=', 'po.order_id')
            ->leftJoin('vendors as v', 'v.id', '=', 'po.vendor_id')
            ->leftJoin('delivery_companies as dc', 'o.delivery_company_id', '=', 'dc.id')
            ->join('wing_transactions as wt', 'wt.id', '=', 'o.wing_transaction_id')
            ->join('members as m', 'm.id', '=', 'wt.member_id')
            ->join('carts as c', 'c.id', '=', 'o.cart_id')
            ->join('minewing_products as mp', 'mp.id', '=', 'c.product_id')
            ->leftJoin('coupang_accounts as ca', function ($join) {
                $join->on('ca.id', '=', 'po.account_id')
                    ->where('po.vendor_id', 40);
            })
            ->leftJoin('smart_store_accounts as ssa', function ($join) {
                $join->on('ssa.id', '=', 'po.account_id')
                    ->where('po.vendor_id', 51);
            })
            ->whereIn('mp.sellerID', $vendors)
            ->select(
                'm.username as member_username',
                'c.quantity as quantity',
                'o.receiver_name as receiver_name',
                'o.receiver_phone as receiver_phone',
                'o.receiver_address as receiver_address',
                'v.name as vendor_name',
                'v.name_eng as vendor_name_eng',
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
                'o.type as type',
                'o.created_at as createdAt',
                'o.updated_at as updatedAt',
                'o.price_then as productPrice',
                'o.shipping_fee_then as shippingFee',
                DB::raw('IF(po.order_id IS NOT NULL, true, false) as isExist'),
                'o.tracking_number as trackingNumber',
                'dc.name as deliveryCompany',
                'mp.productCode as productCode',
                'o.admin_remark as adminRemark',
                'o.bundle_quantity_then as bundleQuantity',
                'o.delivery_status as deliveryStatus',
                DB::raw('COALESCE(ca.username, ssa.username) as username')
            );

        $oneMonthAgo = Carbon::now()->subMonth();
        switch ($orderStatus) {
            case 'PAID_REQUEST':
                $query->where('o.delivery_status', 'PENDING')
                    ->where('o.type', 'PAID')
                    ->where('o.requested', 'N');
                break;
            case 'PAID_PROCESS':
                $query->where('o.delivery_status', 'PENDING')
                    ->where('o.type', 'PAID')
                    ->where('o.requested', 'Y');
                break;
            case 'PAID_COMPLETE':
                $query->where('o.delivery_status', 'COMPLETE')
                    ->where('o.type', 'PAID')
                    ->where('o.updated_at', '>=', $oneMonthAgo);
                break;
            case 'CANCEL_COMPLETE':
                $query->where('o.type', 'CANCELLED')
                    ->where('o.updated_at', '>=', $oneMonthAgo);
                break;
            case 'RETURN_REQUEST':
                $query->where('o.delivery_status', 'PENDING')
                    ->where('o.type', 'REFUND')
                    ->where('o.requested', 'N');
                break;
                // case 'RETURN_PROCESS':
                //     $query->where('o.delivery_status', 'PENDING')
                //         ->where('o.type', 'REFUND')
                //         ->where('o.requested', 'Y');
                //     break;
            case 'RETURN_COMPLETE':
                $query->where('o.delivery_status', 'COMPLETE')
                    ->where('o.type', 'REFUND')
                    ->where('o.updated_at', '>=', $oneMonthAgo);
                break;
            case 'EXCHANGE_REQUEST':
                $query->where('o.delivery_status', 'PENDING')
                    ->where('o.type', 'EXCHANGE')
                    ->where('o.requested', 'N');
                break;
            case 'EXCHANGE_PROCESS':
                $query->where('o.delivery_status', 'PENDING')
                    ->where('o.type', 'EXCHANGE')
                    ->where('o.requested', 'Y');
                break;
            case 'EXCHANGE_COMPLETE':
                $query->where('o.delivery_status', 'COMPLETE')
                    ->where('o.type', 'EXCHANGE')
                    ->where('o.updated_at', '>=', $oneMonthAgo);
                break;
        }

        $orders = [];
        $query->orderBy('o.created_at', 'asc')->chunk(200, function ($chunk) use (&$orders) {
            foreach ($chunk as $order) {
                $orders[] = $order;
            }
        });

        return $orders;
    }



    private function transformOrderDetails($order)
    {
        $product  = DB::table('minewing_products as mp')
            ->where('mp.id', $order->productId)
            ->first();
        $orderType = '신규주문';
        switch ($order->type) {
            case 'REFUND':
                $orderType = '환불';
                break;
            case 'EXCHANGE':
                $orderType = '교환';
                break;
        }
        $orderDate = ($order->deliveryStatus === 'COMPLETE') ? '발주일자 :<br>' . $order->updatedAt : '수집일자 :<br>' . $order->createdAt;
        $productPrice = $order->productPrice;
        $shippingRate = $order->bundleQuantity < 1 ? 1 : ceil($order->quantity / $order->bundleQuantity);
        $shippingAmount = $order->shippingFee * $shippingRate;
        $amount = $product ? $productPrice * $order->quantity + $shippingAmount : null;
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
            'productPrice' => $productPrice,
            'shippingFee' => $shippingAmount,
            'quantity' => $order->quantity,
            'amount' => $amount,
            'orderType' => $orderType,
            'senderNickName' => $order->senderNickName,
            'senderPhone' => $order->senderPhone,
            'senderEmail' => $order->senderEmail,
            'senderName' => $order->lastName . $order->firstName,
            'productOrderNumber' => $order->productOrderNumber,
            'isPartner' => $order->isExist,
            'isActive' => $product->isActive,
            'receiverRemark' => $order->receiverRemark,
            'orderDate' => $orderDate,
            'trackingNumber' => $order->trackingNumber,
            'deliveryCompany' => $order->deliveryCompany,
            'productCode' => $order->productCode,
            'adminRemark' => $order->adminRemark ? $order->adminRemark : null,
            'username' => $order->username ? $order->username : '도매윙'
        ];
    }
    private function getAllPartners()
    {
        return DB::table('partners as p')
            ->join('partner_domewing_accounts as pda', 'p.id', '=', 'pda.partner_id')
            ->where('p.is_active', 'ACTIVE')
            ->where('pda.is_active', 'Y')
            ->select('p.*', 'pda.domewing_account_id')
            ->get();
    }
    private function getAllOpenMarkets()
    {
        return DB::table('vendors')
            ->where('is_active', 'ACTIVE')
            ->where('type', 'OPEN_MARKET')
            ->whereIn('id', [40, 51])
            ->get();
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

    private function callSmart_storeOrderApi($id)
    {
        $controller = new SmartStoreOrderController();
        return $controller->index($id);
    }
    private function callCoupangOrderApi($id)
    {
        $controller = new CoupangOrderController();
        return $controller->index($id);
    }
    private function callSt11OrderApi($id)
    {
        $controller = new St11OrderController();
        return $controller->index($id);
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
            // ->where('is_active', 'Y')
            ->select('id')
            ->first();
    }
    private function getCoupangUploadedProductId($productId)
    {
        return DB::table('coupang_uploaded_products')
            ->where('product_id', $productId)
            // ->where('is_active', 'Y')
            ->select('id')
            ->first();
    }
    private function getSt11UploadedProductId($productId)
    {
        return DB::table('st11_uploaded_products')
            ->where('product_id', $productId)
            // ->where('is_active', 'Y')
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
    private function isExistSt11Account($partnerId)
    {
        return DB::table('st11_accounts')
            ->where('partner_id', $partnerId)
            ->where('is_active', 'ACTIVE')
            ->exists();
    }
}
