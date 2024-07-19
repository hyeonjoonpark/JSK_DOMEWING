<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OpenMarketCreateOrder extends Controller
{
    public function createOrder($apiResults, $memberId, $vendorId, $openMarketEngName)
    {
        try {
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
            foreach ($groupedResults as $orderId => $orders) { //orderId기준 반복문 진행
                $totalPrice = 0;
                $cartIds = [];
                foreach ($orders as $order) {
                    $product = $this->getProduct($order['productCode']);
                    if (!$product) continue; //셀러가 우리 제품 말고 다른걸 올릴 수 있음
                    $cart = $this->storeCart($memberId, $product->id, $order['quantity']);
                    $cartId = $cart['data']['cartId'];
                    $cartCode = $this->getCartCode($cartId);
                    $totalPrice += $this->getCartAmount($cartCode, $product->sellerID); // wing_transaction amount구하기
                    $cartIds[] = $cartId;  //cartId 리스트로 보관
                }
                if ($totalPrice === 0) continue;
                $wingTransaction =  $this->storeWingTransaction($memberId, 'PAYMENT', $totalPrice, $remark = ''); //윙 트랜잭션 테이블 insert
                $wingTransactionId = $wingTransaction['data']['wingTransactionId']; //저장한 데이터의 id값
                foreach ($orders as $index => $order) {
                    $product = $this->getProduct($order['productCode']);
                    if (!$product) continue;
                    $priceThen = $this->getSalePrice($product->id, $product->sellerID);
                    $orderResult = $this->storeOrder($wingTransactionId, $cartIds[$index], $order['receiverName'], $order['receiverPhone'], $order['address'], $order['remark'], $priceThen, $product->shipping_fee, $product->bundle_quantity, $order['orderDate']);
                    $orderId = $orderResult['data']['orderId']; //order 테이블 insert하고 id값 챙기기
                    $uploadedProductMethod = 'get' . ucfirst($openMarketEngName) . 'UploadedProductId';  // 오픈마켓별 업로드된 상품인지 조회하려고 메소드명 지정
                    $uploadedProduct = call_user_func([$this, $uploadedProductMethod], $product->id); // 해당 오픈마켓 업로드테이블에서 업로드된 상품인지 확인하고 id값 가져옴
                    $uploadedProductId = $uploadedProduct->id;
                    $this->storePartnerOrder($orderId, $vendorId, $order['accountId'], $uploadedProductId, $priceThen, $product->shipping_fee, $order['orderId'], $order['productOrderId'], $order['accountId']); //partner_orders 테이블 insert
                }
            }
            return [
                'status' => true,
                'message' => '주문 저장에 성공하였습니다'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '주문 생성 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
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
    private function getSalePrice($productId, $sellerID)
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
        if ($sellerID == 5) $margin = DB::table('sellwing_config')->where('id', 3)->value('value');
        $marginRate = ($margin / 100) + 1;
        return ceil($productPrice * $marginRate);
    }
    private function getCartAmount($cartCode, $sellerID)
    {
        $cart = DB::table('carts AS c')
            ->join('minewing_products AS mp', 'mp.id', '=', 'c.product_id')
            ->where('c.code', $cartCode)
            ->first();

        $salePrice = $this->getSalePrice($cart->product_id, $sellerID);
        $shippingRate = $cart->bundle_quantity === 0 ? 1 : ceil($cart->quantity / $cart->bundle_quantity);
        return $salePrice * $cart->quantity + $cart->shipping_fee * $shippingRate;
    }
    private function getCartCode($cartId)
    {
        return DB::table('carts')
            ->where('id', $cartId)
            ->value('code');
    }
    private function getProduct($productCode)
    {
        return DB::table('minewing_products')
            ->where('productCode', $productCode)
            ->first();
    }
    private function getSmart_storeUploadedProductId($productId)
    {
        return DB::table('smart_store_uploaded_products')
            ->where('product_id', $productId)
            ->select('id')
            ->first();
    }
    private function getCoupangUploadedProductId($productId)
    {
        return DB::table('coupang_uploaded_products')
            ->where('product_id', $productId)
            ->select('id')
            ->first();
    }
    private function getSt11UploadedProductId($productId)
    {
        return DB::table('st11_uploaded_products')
            ->where('product_id', $productId)
            ->select('id')
            ->first();
    }
}
