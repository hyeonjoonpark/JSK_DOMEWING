<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WingController extends Controller
{
    public function getBalance(int $memberId): int
    {
        $depositAmount = DB::table('wing_transactions')
            ->where('member_id', $memberId)
            ->where('type', 'DEPOSIT')
            ->sum('amount');
        $withdrawalAmount = DB::table('wing_transactions AS wt')
            ->join('withdrawal_details AS wd', 'wd.wing_transaction_id', '=', 'wt.id')
            ->where('wt.member_id', $memberId)
            ->where('wd.status', 'APPROVED')
            ->sum('wt.amount');
        $paidAmount = DB::table('wing_transactions AS wt')
            ->join('orders AS o', 'o.wing_transaction_id', '=', 'wt.id')
            ->where('member_id', $memberId)
            ->where('o.type', 'PAID')
            ->where('o.status', 'APPROVED')
            ->distinct()
            ->sum('wt.amount');
        $refundAmount = DB::table('orders AS o')
            ->join('wing_transactions AS wt', 'o.wing_transaction_id', '=', 'wt.id')
            ->where('wt.member_id', $memberId)
            ->where('o.type', 'REFUND')
            ->where('o.status', 'APPROVED')
            ->sum('wt.amount');
        $exchangeAmount = DB::table('orders AS o')
            ->join('wing_transactions AS wt', 'o.wing_transaction_id', '=', 'wt.id')
            ->where('wt.member_id', $memberId)
            ->where('o.type', 'EXCHANGE')
            ->where('o.status', 'APPROVED')
            ->sum('wt.amount');
        $balance = $depositAmount - $withdrawalAmount - $paidAmount + $refundAmount - $exchangeAmount;
        return $balance;
    }
    public function saveOrder($order, $domewingAndPartner, $domewingUser, $totalAmountRequired)
    {
        // 트랜잭션 시작
        DB::beginTransaction();
        try {
            // 상품 정보 가져오기
            $product = $this->getProduct($order['productCode']);
            // 프로모션 여부 확인 및 금액 계산
            $isPromotion = $this->isPromotionProduct($order['productCode']);
            $amount = $isPromotion ? $this->getPromotionProduct($product) : $this->getAmmount($order['productCode']);


            $wintTransactionId = DB::table('wing_transactions')->insertGetId([
                'member_id' => $domewingAndPartner->domewing_account_id,
                'order_number' => Str::uuid(),
                'type' => 'PAYMENT',
                'amount' => $totalAmountRequired
                // 'is_active' => 'PAID',
                // 'price_at' => $order['totalPaymentAmount'],
                // 'shipping_at' => $order['deliveryFeeAmount']
            ]);

            $cartId = DB::table('carts')->insertGetId([
                'member_id' => $domewingAndPartner->domewing_account_id,
                'product_id' => $product->product_id,
                'quantity' => $order['quantity'],
                'code' => Str::uuid(),
                'status' => 'PAID'
                // 'order_id' => $order['orderId'],
                // 'product_order_id' => $order['productOrderId'],
                // 'user_id' => $domewingAndPartner->domewing_account_id,
                // 'cart_id' => $cartId,
                // 'order_from' => $order['marketEngName']
            ]);
            $orderId = DB::table('orders')->insertGetId([
                'wing_transaction_id' => $wintTransactionId,
                'cart_id' => $cartId,
                'product_order_number' => Str::uuid(),
                'delivery_status' => 'PENDING',
                // 'price_then'
                // 'shipping_fee_then'
            ]);
            DB::table('order_details')->insert([
                'order_id' => $orderId,
                'type' => 'PAID'
            ]);
            DB::table('partner_orders')->insert([]);



            // transaction_wing 테이블에 트랜잭션 삽입
            $uuid = Str::uuid()->toString();
            $uuidWithoutHyphens = str_replace('-', '', $uuid);
            $refNo = 'TRX' . $uuidWithoutHyphens;
            DB::table('transaction_wing')->insert([
                'ref_no' => $refNo,
                'member_id' => $domewingAndPartner->domewing_account_id,
                'amount' => $amount,
                'type' => 'ORDER',
                'order_id' => $order['orderId'],
                'product_order_id' => $order['productOrderId'],
                'status' => 'APPROVED'
            ]);
            // delivery_details 테이블에 배송 정보 삽입
            DB::table('delivery_details')->insert([
                'transaction_id' => $refNo,
                'contact_name' => $order['receiverName'],
                'phone_number' => $order['receiverPhone'],
                'email' => $domewingUser->email,
                'address_name' => $order['addressName'],
                'address' => $order['address']
            ]);
            // 모든 작업이 성공적으로 완료되면 커밋
            DB::commit();
            return ['status' => true, 'message' => '신규주문 저장 성공.'];
        } catch (\Exception $e) {
            // 에러 발생 시 롤백
            DB::rollBack();
            return ['status' => false, 'message' => '신규주문 저장 에러: ' . $e->getMessage()];
        }
    }

    private function getProduct($productCode)
    {
        $product = DB::table('minewing_products')
            ->where('productCode', $productCode)
            ->first();
        return $product;
    }
    private function getMarginRate($title)
    {
        $marginPer = DB::table('sellwing_config')
            ->where('title', $title)
            ->first();
        $marginRate = 1 + ($marginPer->value / 100);
        return $marginRate;
    }
    private function isPromotionProduct($productCode)
    {
        $product = $this->getProduct($productCode);
        $isPromotion = DB::table('promotion_products')
            ->where('product_id', $product->id)
            ->where('is_active', 'Y')
            ->exists();
        return $isPromotion;
    }
    private function getPromotionProduct($product)
    {
        $promotionProduct = DB::table('promotion_products')
            ->where('product_id', $product->id)
            ->where('is_active', 'Y')
            ->first();
        return $promotionProduct;
    }
    private function getAmmount($productCode)
    {
        $product = $this->getProduct($productCode);
        $ammount = $product->productPrice * $this->getMarginRate('margin');
        return $ammount;
    }
}
