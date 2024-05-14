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
            ->join('order_details AS od', 'od.order_id', '=', 'o.id')
            ->where('wt.member_id', $memberId)
            ->where('od.type', 'PAID')
            ->sum('wt.amount');
        $refundAmount = DB::table('wing_transactions AS wt')
            ->join('orders AS o', 'o.wing_transaction_id', '=', 'wt.id')
            ->join('order_details AS od', 'od.order_id', '=', 'o.id')
            ->join('exception_details AS ed', 'ed.order_detail_id', '=', 'od.id')
            ->where('wt.member_id', $memberId)
            ->where('ed.type', '단순변심')
            ->sum(DB::raw('o.shipping_fee_then * 2'));
        $exchangeAmount = DB::table('wing_transactions AS wt')
            ->join('orders AS o', 'o.wing_transaction_id', '=', 'wt.id')
            ->join('order_details AS od', 'od.order_id', '=', 'o.id')
            ->join('exception_details AS ed', 'ed.order_detail_id', '=', 'od.id')
            ->select(
                DB::raw('SUM(CASE WHEN ed.type = "단순변심" THEN wt.amount + o.shipping_fee_then ELSE 0 END) AS simple_mind_sum'),
                DB::raw('SUM(CASE WHEN ed.type != "단순변심" THEN wt.amount ELSE 0 END) AS other_sum')
            )
            ->where('wt.member_id', $memberId)
            ->first();
        $balance = $depositAmount - $withdrawalAmount - $paidAmount - $refundAmount - $exchangeAmount->simple_mind_sum - $exchangeAmount->other_sum;
        return $balance;
    }
    public function saveOrder($order, $domewingAndPartner, $domewingUser)
    {
        // 트랜잭션 시작
        DB::beginTransaction();
        try {
            // 상품 정보 가져오기
            $product = $this->getProduct($order['productCode']);
            // 프로모션 여부 확인 및 금액 계산
            $isPromotion = $this->isPromotionProduct($order['productCode']);
            $amount = $isPromotion ? $this->getPromotionProduct($product) : $this->getAmmount($order['productCode']);


            // shopping_cart 테이블에 주문 삽입
            $cartId = DB::table('shopping_cart')->insertGetId([
                'product_id' => $product->id,
                'user_id' => $domewingAndPartner->domewing_account_id,
                'quantity' => $order['quantity'],
                'is_active' => 'PAID',
                'price_at' => $order['totalPaymentAmount'],
                'shipping_at' => $order['deliveryFeeAmount']
            ]);
            // order_cart 테이블에 주문 삽입
            DB::table('order_cart')->insert([
                'order_id' => $order['orderId'],
                'product_order_id' => $order['productOrderId'],
                'user_id' => $domewingAndPartner->domewing_account_id,
                'cart_id' => $cartId,
                'order_from' => $order['marketEngName']
            ]);
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
