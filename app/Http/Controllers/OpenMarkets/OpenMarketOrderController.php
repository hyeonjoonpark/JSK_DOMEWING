<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangExchangeController;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangOrderController;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangReturnController;
use App\Http\Controllers\OpenMarkets\St11\St11OrderController;
use App\Http\Controllers\SmartStore\SmartStoreExchangeController;
use App\Http\Controllers\SmartStore\SmartStoreOrderController;
use App\Http\Controllers\SmartStore\SmartStoreReturnController;
use App\Http\Controllers\WingController;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OpenMarketOrderController extends Controller
{
    public function index()
    {
        try {
            $allPartners = $this->getAllPartners(); // 모든 파트너 조회
            $allOpenMarkets = $this->getAllOpenMarkets(); // 활성화 중인 오픈마켓 조회
            foreach ($allPartners as $partner) { // 모든 파트너 반복문
                $memberId = $partner->domewing_account_id;
                foreach ($allOpenMarkets as $openMarket) { // 오픈마켓 반복문
                    $openMarketEngName = $openMarket->name_eng; // 해당 오픈마켓 영어이름 구하기
                    $isExistAccount = 'isExist' . ucfirst($openMarketEngName) . 'Account';
                    $isExistOpenMarketAccount = call_user_func([$this, $isExistAccount], $partner->id); // 해당 파트너가 해당 오픈마켓 아이디가 있는지 확인
                    if (!$isExistOpenMarketAccount) continue;
                    $methodName = 'call' . ucfirst($openMarketEngName) . 'OrderApi'; // 오픈마켓별 주문내역조회 API 호출 메소드명 지정
                    $apiResults = call_user_func([$this, $methodName], $partner->id, $startDate = null, $endDate = null); // API 결과 저장
                    if ($apiResults === false) continue; // API 결과값이 비어있으면 continue
                    $openMarketCreateOrder = new OpenMarketCreateOrder();
                    $resultCreateOrder = $openMarketCreateOrder->createOrder($apiResults, $memberId, $openMarket->id, $openMarketEngName);
                    if (!$resultCreateOrder['status']) {
                        return [
                            'status' => false,
                            'message' => $resultCreateOrder['message'],
                            'data' => $resultCreateOrder
                        ];
                    }
                    if ($openMarketEngName === 'coupang' || $openMarketEngName === 'smart_store') {
                        $returnMethod = 'get' . ucfirst($openMarketEngName) . 'ReturnOrder';
                        $returnResult = call_user_func([$this, $returnMethod], $partner->id);
                        if (!$returnResult['status']) {
                            return [
                                'status' => false,
                                'message' => $returnResult['message'],
                                'data' => $returnResult
                            ];
                        }
                    }
                    if ($openMarketEngName === 'coupang' || $openMarketEngName === 'smart_store') {
                        $exchangeMethod = 'get' . ucfirst($openMarketEngName) . 'ExchangeOrder';
                        $exchangeResult = call_user_func([$this, $exchangeMethod], $partner->id);
                        if (!$exchangeResult['status']) {
                            return [
                                'status' => false,
                                'message' => $exchangeResult['message'],
                                'data' => $exchangeResult
                            ];
                        }
                    }
                }
            }
            return [
                'status' => true,
                'message' => '새로운 주문 저장이 완료되었습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '처리 중 오류가 발생하였습니다: ' . $e->getMessage()
            ];
        }
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
            $startOn = $request->input('startOn');
            $endOn = $request->input('endOn');
            $vendors = $request->input('vendors');
            $orderStatus = $request->input('orderStatus');
            $orders = $this->getOrders($vendors, $orderStatus, $startOn, $endOn);
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
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $results = [];
        $partner = $this->getPartner($domewingAndPartner->partner_id);
        $domewingUser = $this->getDomewingUser($domewingAndPartner->domewing_account_id);
        foreach ($orderwingEngNameLists as $orderwingEngNameList) {
            $methodName = 'call' . ucfirst($orderwingEngNameList) . 'OrderApi';
            if (method_exists($this, $methodName)) {
                $apiResult = call_user_func([$this, $methodName], $partner->id, $startDate, $endDate);
                if (isset($apiResult) && is_array($apiResult)) {
                    foreach ($apiResult as $order) {
                        if ($order['productOrderStatus'] == "결제완료") {
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
        if ($targetStatus == 'awaiting-shipment' && $orderType == 'EXCHANGE') {
            $setAwaitingController = new OpenMarketExchangeController();
            return $setAwaitingController->setAwaitingShipmentStatus($request->productOrderNumber);
        }
        if ($targetStatus == 'shipment-complete' && $orderType == 'EXCHANGE') {
            $exchangeController = new OpenMarketExchangeController();
            return $exchangeController->saveExchangeShipment($request);
        }
        if ($targetStatus == 'order-cancel' && $orderType == 'EXCHANGE') {
            $exchangeController = new OpenMarketExchangeController();
            return $exchangeController->cancelExchange($request);
        }
        if ($targetStatus == 'accept-cancel' && $orderType == 'EXCHANGE') {
            $cancelOrderController = new OpenMarketExchangeController();
            return $cancelOrderController->acceptCancel($productOrderNumber, $remark);
        }
        if ($targetStatus == 'awaiting-shipment' && $orderType == 'REFUND') {
            $setAwaitingController = new OpenMarketRefundController();
            return $setAwaitingController->setAwaitingShipmentStatus($request->productOrderNumber);
        }
        if ($targetStatus == 'shipment-complete' && $orderType == 'REFUND') {
            $exchangeController = new OpenMarketRefundController();
            return $exchangeController->saveRefundShipment($request);
        }
        if ($targetStatus == 'order-cancel' && $orderType == 'REFUND') {
            $exchangeController = new OpenMarketRefundController();
            return $exchangeController->cancelRefund($request);
        }
        if ($targetStatus == 'accept-cancel' && $orderType == 'REFUND') {
            $cancelOrderController = new OpenMarketRefundController();
            return $cancelOrderController->acceptCancel($productOrderNumber, $remark);
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
            $cancelOrderController = new OpenMarketCancelController();
            return $cancelOrderController->cancelOrder($productOrderNumber, $remark);
        }
        if ($targetStatus == 'accept-cancel') {
            $cancelOrderController = new OpenMarketCancelController();
            return $cancelOrderController->acceptCancel($productOrderNumber, $remark);
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
                $lowBalanceAccounts[] = $name;
            }
        }
        return $lowBalanceAccounts;
    }
    private function getOrders($vendors, $orderStatus, $startOn, $endOn)
    {
        $endOnDate = Carbon::createFromFormat('Y-m-d', $endOn)->endOfDay();
        $startOnDate = Carbon::createFromFormat('Y-m-d', $startOn)->startOfDay();
        $query = DB::table('orders as o')
            ->leftJoin('partner_orders as po', 'o.id', '=', 'po.order_id')
            ->leftJoin('delivery_companies as dc', 'o.delivery_company_id', '=', 'dc.id')
            ->leftJoin('vendors as v', 'v.id', '=', 'po.vendor_id')
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
            ->leftJoin('st11_accounts as sa', function ($join) {
                $join->on('sa.id', '=', 'po.account_id')
                    ->where('po.vendor_id', 54);
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
                DB::raw('COALESCE(ca.username, ssa.username, sa.username) as username')
            );
        switch ($orderStatus) {
            case 'PAID_REQUEST':
                $query->where('o.delivery_status', 'PENDING')
                    ->where('wt.status', 'APPROVED')
                    ->where('o.type', 'PAID')
                    ->where('o.requested', 'N')
                    ->whereBetween('o.created_at', [$startOnDate, $endOnDate]);
                break;
            case 'PAID_PROCESS':
                $query->where('o.delivery_status', 'PENDING')
                    ->where('wt.status', 'APPROVED')
                    ->where('o.type', 'PAID')
                    ->where('o.requested', 'Y')
                    ->whereBetween('o.created_at', [$startOnDate, $endOnDate]);
                break;
            case 'PAID_COMPLETE':
                $query->where('o.delivery_status', 'COMPLETE')
                    ->where('wt.status', 'APPROVED')
                    ->where('o.type', 'PAID')
                    ->whereBetween('o.updated_at', [$startOnDate, $endOnDate]);
                break;
            case 'CANCEL_COMPLETE':
                $query->where(function ($query) {
                    $query->where('o.type', 'CANCELLED')
                        ->orWhere(function ($query) {
                            $query->where('o.type', '!=', 'CANCELLED')
                                ->where('wt.status', 'REJECTED');
                        });
                })->whereBetween('o.updated_at', [$startOnDate, $endOnDate]);
                break;
            case 'RETURN_REQUEST':
                $query->where('o.delivery_status', 'PENDING')
                    ->where('wt.status', 'PENDING')
                    ->where('o.type', 'REFUND')
                    ->where('o.requested', 'N')
                    ->where(function ($query) {
                        $query->where('po.vendor_id', '!=', 40)
                            ->orWhereNull('po.vendor_id');
                    })
                    ->whereBetween('o.created_at', [$startOnDate, $endOnDate]);
                break;
            case 'RETURN_PROCESS':
                $query->where('o.delivery_status', 'PENDING')
                    ->where('wt.status', 'PENDING')
                    ->where('o.type', 'REFUND')
                    ->where('o.requested', 'Y')
                    ->whereBetween('o.created_at', [$startOnDate, $endOnDate]);
                break;
            case 'RETURN_COMPLETE':
                $query->where('o.delivery_status', 'COMPLETE')
                    ->where('wt.status', 'APPROVED')
                    ->where('o.type', 'REFUND')
                    ->whereBetween('o.updated_at', [$startOnDate, $endOnDate]);
                break;
            case 'EXCHANGE_REQUEST':
                $query->where('o.delivery_status', 'PENDING')
                    ->where('wt.status', 'PENDING')
                    ->where('o.type', 'EXCHANGE')
                    ->where('o.requested', 'N')
                    ->where(function ($query) {
                        $query->where('po.vendor_id', '!=', 40)
                            ->orWhereNull('po.vendor_id');
                    })
                    ->whereBetween('o.created_at', [$startOnDate, $endOnDate]);
                break;
            case 'EXCHANGE_PROCESS':
                $query->where('o.delivery_status', 'PENDING')
                    ->where('wt.status', 'PENDING')
                    ->where('o.type', 'EXCHANGE')
                    ->where('o.requested', 'Y')
                    ->whereBetween('o.created_at', [$startOnDate, $endOnDate]);
                break;
            case 'EXCHANGE_COMPLETE':
                $query->where('o.delivery_status', 'COMPLETE')
                    ->where('wt.status', 'APPROVED')
                    ->where('o.type', 'EXCHANGE')
                    ->whereBetween('o.updated_at', [$startOnDate, $endOnDate]);
                break;
            case 'COUPANG_EXCHANGE':
                $query->where('o.delivery_status', 'PENDING')
                    ->where('wt.status', 'PENDING')
                    ->where('o.type', 'EXCHANGE')
                    ->where('o.requested', 'N')
                    ->where('po.vendor_id', 40)
                    ->whereBetween('o.created_at', [$startOnDate, $endOnDate]);
                break;
            case 'COUPANG_RETURN':
                $query->where('o.delivery_status', 'PENDING')
                    ->where('wt.status', 'PENDING')
                    ->where('o.type', 'RETURN')
                    ->where('o.requested', 'N')
                    ->where('po.vendor_id', 40)
                    ->whereBetween('o.created_at', [$startOnDate, $endOnDate]);
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
                $orderType = '반품';
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
            ->whereIn('id', [40, 51, 54])
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
    private function getDomewingAndPartners($partnerId = null)
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
    private function getSmart_storeReturnOrder($partnerId)
    {
        $controller = new SmartStoreReturnController();
        return $controller->index($partnerId);
    }
    private function getCoupangReturnOrder($partnerId)
    {
        $controller = new CoupangReturnController();
        return $controller->index($partnerId);
    }
    private function getSmart_storeExchangeOrder($partnerId)
    {
        $controller = new SmartStoreExchangeController();
        return $controller->index($partnerId);
    }
    private function getCoupangExchangeOrder($partnerId)
    {
        $controller = new CoupangExchangeController();
        return $controller->index($partnerId);
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
