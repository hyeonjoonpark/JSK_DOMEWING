<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OpenMarketExchangeRefundController extends Controller
{
    // 교환 및 환불 신청
    public function createExchangeRefund($data)
    {
        $partnerOriginOrder = DB::table('partner_orders')->where('product_order_number', $data['productOrderNumber'])->first();
        // 주문 데이터 가져오기
        $order = DB::table('orders AS o')
            ->join('carts AS c', 'c.id', '=', 'o.cart_id')
            ->join('minewing_products AS mp', 'mp.id', '=', 'c.product_id')
            ->where('o.id', $partnerOriginOrder->order_id)
            ->first();
        // 요청 수량 검증
        if ($data['quantity'] > $order->quantity) {
            return [
                'status' => false,
                'message' => '요청 수량은 주문 수량보다 적거나 같아야 합니다.'
            ];
        }
        // 필요한 데이터 준비
        $amount = $this->calculateAmount($data['requestType'], $data['reasonType'], $data['quantity'], $order);
        $data = array_merge($data, [
            'memberId' => $order->member_id,
            'productId' => $order->product_id,
            'cartCode' => Str::uuid(),
            'orderNumber' => Str::uuid(),
            'amount' => $amount,
            'receiverName' => $data['receiverName'],
            'receiverPhone' => $data['receiverPhone'],
            'receiverAddress' => $data['receiverAddress'],
            'priceThen' => $order->price_then,
            'shippingFeeThen' => $order->shipping_fee_then,
            'bundleQuantityThen' => $order->bundle_quantity_then,
            'productOrderNumber' => $order->product_order_number,
            'createdAt' => $order->createdAt
        ]);
        // 교환 및 환불 요청 저장
        return $this->storeExchangeRefund($data, $partnerOriginOrder);
    }
    private function calculateAmount($requestType, $reasonType, $quantity, $order)
    {
        if ($requestType === 'EXCHANGE') {
            $shippingRate = $order->bundle_quantity_then < 1 ? 1 : ceil($quantity / $order->bundle_quantity_then);
            $shippingAmount = $order->shipping_fee_then * $shippingRate;
            return $reasonType === "단순변심" ? $shippingAmount : 0;
        }
        if ($requestType === 'REFUND') {
            $productAmount = $order->price_then * $quantity;
            $shippingRate = $order->bundle_quantity_then < 1 ? 1 : ceil($quantity / $order->bundle_quantity_then);
            $shippingAmount = $order->shipping_fee_then * $shippingRate;
            return $reasonType === '단순변심' ? $productAmount - $shippingAmount : $productAmount + $shippingAmount;
        }
        return 0;
    }
    private function storeExchangeRefund($data, $partnerOriginOrder)
    {
        DB::beginTransaction();
        try {
            $cartId = DB::table('carts')
                ->insertGetId([
                    'member_id' => $data['memberId'],
                    'product_id' => $data['productId'],
                    'quantity' => $data['quantity'],
                    'code' => $data['cartCode'],
                    'status' => 'PAID'
                ]);
            $wingTransactionId = DB::table('wing_transactions')
                ->insertGetId([
                    'member_id' => $data['memberId'],
                    'order_number' => $data['orderNumber'],
                    'type' => 'PAYMENT',
                    'status' => 'PENDING',
                    'amount' => $data['amount']
                ]);
            $orderId = DB::table('orders')
                ->insertGetId([
                    'wing_transaction_id' => $wingTransactionId,
                    'cart_id' => $cartId,
                    'product_order_number' => Str::uuid(),
                    'receiver_name' => $data['receiverName'],
                    'receiver_phone' => $data['receiverPhone'],
                    'receiver_address' => $data['receiverAddress'],
                    'type' => $data['requestType'],
                    'price_then' => $data['priceThen'],
                    'shipping_fee_then' => $data['shippingFeeThen'],
                    'bundle_quantity_then' => $data['bundleQuantityThen'],
                    'created_at' => $data['createdAt']
                ]);
            DB::table('partner_orders')
                ->insertGetId([
                    'order_id' => $orderId,
                    'vendor_id' => $partnerOriginOrder->vendor_id,
                    'account_id' => $partnerOriginOrder->account_id,
                    'uploaded_product_id' => $partnerOriginOrder->uploaded_product_id,
                    'price_then' => $partnerOriginOrder->price_then,
                    'shipping_fee_then' => $partnerOriginOrder->shipping_fee_then,
                    'order_number' => $partnerOriginOrder->order_number,
                    'product_order_number' => $data['newProductOrderNumber'],
                ]);
            DB::table('order_details')
                ->insert([
                    'order_id' => $orderId,
                    'type' => $data['reasonType'],
                    'quantity' => $data['quantity'],
                    'reason' => $data['reason']
                ]);
            DB::table('orders')
                ->where('product_order_number', $data['productOrderNumber'])
                ->update([
                    'has_claimed' => 1
                ]);
            DB::commit();
            return [
                'status' => true,
                'message' => '해당 요청을 성공적으로 전송했습니다.'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => '교환 및 환불 요청을 저장하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
}
