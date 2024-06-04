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
    public function index()
    {
    }
    // 교환 및 환불 신청
    public function createExchangeRefund(Request $request)
    {
        // 유효성 검사
        $validator = Validator::make($request->all(), $this->rules(), $this->messages()); //request 검증, rules의 조건을 지켜야하고 에러 사항 발생시 messages값
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }
        $data = $request->only(['requestType', 'reasonType', 'reason', 'productOrderNumber', 'quantity']); // data에 request의 필요한 정보만 할당
        // 주문 데이터 가져오기
        $order = DB::table('orders AS o') //기존의 주문 data 가져오기 포인트는 productOrderNumber 가 일치해야함
            ->join('carts AS c', 'c.id', '=', 'o.cart_id')
            ->join('minewing_products AS mp', 'mp.id', '=', 'c.product_id')
            ->where('product_order_number', $data['productOrderNumber'])
            ->first();

        // 요청 수량 검증
        if ($data['quantity'] > $order->quantity) {
            return [
                'status' => false,
                'message' => '요청 수량은 주문 수량보다 적거나 같아야 합니다.'
            ];
        }

        // 필요한 데이터 준비
        $amount = $this->calculateAmount($data['requestType'], $data['reasonType'], $data['quantity'], $order); //교환 또는 반품에 금액 계산
        $data = array_merge($data, [ //data에 필요한 정보들 배열로 저장
            'memberId' => $order->member_id, //기존 주문의 member_id
            'productId' => $order->product_id, //기존 주문의 상품 id
            'cartCode' => Str::uuid(), //랜덤 값 다시 생성
            'orderNumber' => Str::uuid(), //랜덤 값 다시 생성
            'amount' => $amount, //위에서 구한 금액
            'receiverName' => $order->receiver_name, //기존 주문의 수령인 이름
            'receiverPhone' => $order->receiver_phone, //기존 주문의 수령인 전화번호
            'receiverAddress' => $order->receiver_address, //기존 주문의 수령인 주소
            'priceThen' => $order->price_then, //기존 주문의 가격
            'shippingFeeThen' => $order->shipping_fee, //기존 주문의 배송비
            'bundleQuantityThen' => $order->bundle_quantity //기존 주문의 묶음배송수량
        ]); //여기서 질문 교환 같은 경우 다시 수령할 사람의 정보가 다를 수 있지 않나? 주소랑

        // 교환 및 환불 요청 저장
        return $this->storeExchangeRefund($data);
    }

    private function rules()
    {
        return [
            'requestType' => ['required', 'in:EXCHANGE,REFUND'],
            'reasonType' => ['required', 'in:단순변심,상품불량,배송지연,상품정보와 상이'],
            'reason' => ['required', 'max:255'],
            'productOrderNumber' => ['required', 'exists:orders,product_order_number'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    private function messages()
    {
        return [
            'requestType.required' => '신청 유형을 선택해주세요.',
            'requestType.in' => '유효한 신청 유형을 선택해주세요 (교환, 반품).',
            'reasonType.required' => '사유 유형을 선택해주세요.',
            'reasonType.in' => '유효한 사유 유형을 선택해주세요 (단순변심, 상품불량, 배송지연, 상품정보와 상이).',
            'reason.required' => '사유를 입력해주세요.',
            'reason.max' => '사유는 최대 255자까지 입력 가능합니다.',
            'productOrderNumber.required' => '상품 주문 번호를 입력해주세요.',
            'productOrderNumber.exists' => '유효한 상품 주문 번호가 아닙니다.',
            'quantity.required' => '수량을 입력해주세요.',
            'quantity.integer' => '수량은 정수로 입력해주세요.',
            'quantity.min' => '수량은 최소 1 이상이어야 합니다.'
        ];
    }
    private function calculateAmount($requestType, $reasonType, $quantity, $order)
    {
        if ($requestType === 'EXCHANGE') { //교환 신청일때
            $shippingRate = $order->bundle_quantity < 1 ? 1 : ceil($quantity / $order->bundle_quantity); //묶음수량이 1보다 작으면 즉 0이면 rate는 1임으로 아무 상관없는데 묶음 수량이 1이상일때 개수/묶음수량을 올림처리하여 rate값 구하기
            $shippingAmount = $order->shipping_fee * $shippingRate; //구한 rate를 기반으로 배송비 곱하기 즉 배송비 부과의 개수 하는중
            return $reasonType === "단순변심" ? $shippingAmount : 0; //그런데 단순변심이면 구한 배송비를 부과하고 아니면 배송비=0
        }

        if ($requestType === 'REFUND') { //반품일때 아래의 로직은 교환이랑 같음
            $productAmount = $order->price_then * $quantity;
            $shippingRate = $order->bundle_quantity < 1 ? 1 : ceil($quantity / $order->bundle_quantity);
            $shippingAmount = $order->shipping_fee * $shippingRate;
            return $reasonType === '단순변심' ? $productAmount - $shippingAmount : $productAmount; //단순변심이면 기존 결제금액에 구한 배송비를 뺴서 돌려주고 단순변심이 아니면 결제금액 그대로 돌려주기
        }

        return 0;
    }

    private function storeExchangeRefund($data) // 재가공한 data를 기반으로 데이터 넣기
    {
        DB::beginTransaction(); //여러 테이블에 데이터 저장 및 update시 트랜잭션을 사용하여 중간에 문제 생기면 전부 rollback 가능하게

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
                    'status' => 'PENDING', //PENDING인 이유는 관리자가 승인해야함
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
                    'bundle_quantity_then' => $data['bundleQuantityThen']
                ]);

            DB::table('order_details')
                ->insert([
                    'order_id' => $orderId,
                    'type' => $data['reasonType'], //단순변심인지 귀책사유인지 등
                    'quantity' => $data['quantity'],
                    'reason' => $data['reason'], //세부 사유 ex - 상품이 이상함, 배송이 너무 늦음, 필요없어짐 등
                ]);

            DB::commit();

            return [
                'status' => true, //성공시
                'message' => '해당 요청을 성공적으로 전송했습니다.'
            ];
        } catch (\Exception $e) {
            DB::rollBack(); //문제가 생겼을경우 rollback하며 db 반영할려던거 다시 돌려놓기 즉 아무 저장 x

            return [
                'status' => false, //실패 return 값
                'message' => '교환 및 환불 요청을 저장하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
}
