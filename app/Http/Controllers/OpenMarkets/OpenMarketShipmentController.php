<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangShipmentController;
use App\Http\Controllers\SmartStore\SmartStoreShipmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OpenMarketShipmentController extends Controller
{
    public function saveShipment(Request $request)
    {
        // 프론트 엔드로부터 받아오는 인자들은 trackingNumber, deliveryCompanyId, 그리고 productOrderNumber
        // 그러므로 데이터 유효성 검사도 3개 모두 진행해야 한다.
        // exsits 를 활용함으로써, DB 의 해당 테이블, 해당 컬럼에 실제로 값이 존재하는지 검출한다.
        $validator = Validator::make($request->all(), [
            'trackingNumber' => 'required|string|min:10|max:13',
            'deliveryCompanyId' => 'required|integer|exists:delivery_companies,id',
            'productOrderNumber' => 'required|string|exists:orders,product_order_number'
        ], [ // 각 인자 값들의 유효성 검사들에 대한 에러 메시지 값들을 매겨줘야 한다.
            'trackingNumber.required' => '운송장 번호는 필수 항목입니다.',
            'trackingNumber.string' => '운송장 번호는 문자열이어야 합니다.',
            'trackingNumber.min' => '운송장 번호는 최소 10자여야 합니다.',
            'trackingNumber.max' => '운송장 번호는 최대 13자여야 합니다.',
            'deliveryCompanyId.required' => '배송 회사 ID는 필수 항목입니다.',
            'deliveryCompanyId.integer' => '배송 회사 ID는 정수여야 합니다.',
            'deliveryCompanyId.exists' => '선택한 배송 회사 ID가 존재하지 않습니다.',
            'productOrderNumber.required' => '제품 주문 번호는 필수 항목입니다.',
            'productOrderNumber.string' => '제품 주문 번호는 문자열이어야 합니다.',
            'productOrderNumber.exists' => '선택한 제품 주문 번호가 존재하지 않습니다.',
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                // 데이터 유효성 검사에 실패시, 에러 메시지 중 첫 번째를 출력해줌으로써 사용자에게 구체적으로 어떤 에러 상황인지 명시해준다.
                'message' => $validator->errors()->first(),
                // error 인자에는 errors() 를 통째로 담음으로써, 디버깅 과정에서 개발자가 에러 요소들을 한 번에 파악할 수 있도록 한다.
                'error' => $validator->errors(),
            ];
        }
        // 데이터 유효성 검사를 확실히 잡음으로써, 당당히 각 인자 값들을 별도의 변수로 선언한다.
        // 여기서 쫄아서, input('trackingNumber', null); 과 같은 쫄보... 님 쫄? ㅋㅋ
        $trackingNumber = $request->trackingNumber;
        $deliveryCompanyId = $request->deliveryCompanyId;
        $productOrderNumber = $request->productOrderNumber;
        // 파사드 DB 를 핸들링하는 거라 메소드 분리한 것 좋은데, 서비스 메소드라기에는 인서트나 업데이트가 아니므로,
        // 간단히 이 자리에서 호출한다. (가독성을 위해)
        // 모델을 활용한 엘로퀀트도 호출은 따로 메소드 분리를 하지 않음.
        $order = DB::table('orders')
            ->where('product_order_number', $productOrderNumber)
            ->where('delivery_status', 'PENDING')
            ->where('type', 'PAID')
            ->first();
        if ($order === null) {
            return [
                'status' => false,
                'message' => '취소되었거나, 이미 처리된 주문입니다.'
            ];
        }
        // 이미 존재하는 값인지 검증하려면, 데이터 유효성 단계에서 'unique' 를 활용한다.
        // exists 는 DB 에 반드시 존재하는 값임을 요구, unique 는 DB 에 존재하지 않는 값임을 요구.
        // 예) 회원가입시, email 인자를 받아왔을 때, unique:users,email 을 통해 이미 존재하는 이메일임을 검사할 수 있다.
        // 하지만 이 케이스에서는, 송장번호가 절대 고유값인지를 의심해봐야 한다.
        // 만약, A 택배사의 송장번호가 B 택배사의 송장번호와 동일할 때, 이것을 에러 처리해야 할 것인가? 와 같은 의문에서이다.
        // $isExistTrackingNumber = DB::table('orders')
        //     ->where('tracking_number', $request->input('trackingNumber'))
        //     ->exists();
        // if ($isExistTrackingNumber) {
        //     return [
        //         'status' => false,
        //         'message' => '이미 존재하는 송장번호입니다.',
        //     ];
        // }
        // 마찬가지로, DB 정보를 호출할 때에는 따로 메소드 분리를 하지 않는 것이 가독성에 좋다.
        // 이는 추후 모델을 활용한 엘로퀀트 기법에 습관을 들이기 위함임.
        // DB 파사드 활용시, 호출 또한 엄연히는 메소드 분리를 하는 게 맞긴 함.
        // 근데 그냥 하지마. 우린 모델+엘로퀀트로 간다.
        // 반복하지만, 데이터 유효성 검사를 엄격하게 해야 중복 검증과 같은 프로세스를 스킵하고 가독성을 늘릴 수 있다.

        $openMarket = DB::table('orders as o')
            ->join('partner_orders as po', 'o.id', '=', 'po.order_id')
            ->join('vendors as v', 'po.vendor_id', '=', 'v.id')
            ->where('o.product_order_number', $productOrderNumber)
            ->where('v.is_active', 'ACTIVE')
            ->select('v.*')
            ->first(['v.name_eng', 'v.name']);
        // openMarketEngName ? API 로 운송장 번호 전송 : 다이렉트로 update 서비스 메소드 진행
        if ($openMarket) {
            $method = 'call' . ucfirst($openMarket->name_eng) . 'ShipmentApi';
            $updateApiResult = $this->$method($request);
            // 운송장 번호 API 처리하는 과정에서, update 로 DB 반영하는 부분은 제외했다.
            // 오류가 발생했을시, 이를 실무팀 측에서 오류 보고를 할 수 있기 때문이다.
            // 운송장 번호가 DB 로 처리되버리면 안 됨.
            // 따라서 얼리 리턴 기법으로 DB 처리를 막는다.
            if ($updateApiResult['status'] === false) {
                return $updateApiResult;
            }
        }
        // API 요청에서 아무 문제가 없었거나
        // 단순 도매윙 주문일시, update 메소드를 진행함으로써 DB 에 최종 반영한다.
        return $this->update($order->id, $deliveryCompanyId, $trackingNumber);
    }
    private function update($orderId, $deliveryCompanyId, $trackingNumber)
    {
        try {
            DB::table('orders')
                ->where('id', $orderId)
                ->update([
                    'tracking_number' => $trackingNumber,
                    'delivery_company_id' => $deliveryCompanyId,
                    'delivery_status' => 'COMPLETE',
                ]);
            return [
                'status' => true,
                'message' => '송장번호 입력에 성공하였습니다'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '송장번호를 업데이트하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    // private function getVendorByProductOrderNumber($productOrderNumber)
    // {
    //     return DB::table('orders as o')
    //         ->join('partner_orders as po', 'o.id', '=', 'po.order_id')
    //         ->join('vendors as v', 'po.vendor_id', '=', 'v.id')
    //         ->where('o.product_order_number', $productOrderNumber)
    //         ->where('v.is_active', 'ACTIVE')
    //         ->select('v.*')
    //         ->first();
    // }
    // private function getOrder($productOrderNumber)
    // {
    //     return DB::table('orders as o')
    //         ->where('product_order_number', $productOrderNumber)
    //         ->where('delivery_status', 'PENDING')
    //         ->where('type', 'PAID')
    //         ->first();
    // }
    private function callSmart_storeShipmentApi(Request $request)
    {
        $controller = new SmartStoreShipmentController();
        return $controller->index($request);
    }
    private function callCoupangShipmentApi(Request $request)
    {
        $controller = new CoupangShipmentController();
        return $controller->index($request);
    }
}
