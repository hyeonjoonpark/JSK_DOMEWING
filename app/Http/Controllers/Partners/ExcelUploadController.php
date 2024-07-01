<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class ExcelUploadController extends Controller
{
    public function uploadExcel(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_allow', '-1');
        $memberId = DB::table('partners')
            ->join('partner_domewing_accounts as pda', 'partners.id', '=', 'pda.partner_id')
            ->where('partners.api_token', $request->apiToken)
            ->where('pda.is_active', 'Y')
            ->value('pda.domewing_account_id');
        $validator = Validator::make($request->all(), [
            'orders' => 'required|file|mimes:xlsx'
        ], [
            'orders' => '.xlsx 확장자를 사용하는 올바른 엑셀 파일을 업로드해주세요.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'error' => $validator->errors()->first()
            ];
        }
        $ordersExcelFile = $request->orders;
        return $this->extractOrdersExcelFile($ordersExcelFile, $memberId);
    }
    private function extractOrdersExcelFile($ordersExcelFile, $memberId)
    {
        try {
            $spreadsheet = IOFactory::load($ordersExcelFile->getRealPath());
            $sheet = $spreadsheet->getSheet(0);
            $errors = [];
            $datas = [];
            $highestRow = $sheet->getHighestRow();
            if ($highestRow >= 501) {
                return [
                    'status' => false,
                    'message' => '데이터는 헤더를 제외하고 2번째부터 501번째 행까지, 총 500개의 행 이하이어야 합니다.'
                ];
            }
            for ($i = 2; $i <= $highestRow; $i++) {
                $rowData = $this->getRowData($sheet, $i);
                $validateColumnsResult = $this->validateColumns($rowData);
                if ($validateColumnsResult['status'] === false) {
                    $productCode = $validateColumnsResult['data'];
                    $message = $validateColumnsResult['message'];
                    $errors[] = $productCode . " => " . $message . "<br>";
                } else {
                    $datas[] = $rowData;
                }
            }
            if (count($errors) > 0) {
                return [
                    'status' => false,
                    'message' => '일부 상품에서 오류가 검출되었습니다.',
                    'errors' => $errors
                ];
            }
            foreach ($datas as $data) { //엑셀의 데이터를 이미 검증했기 때문에 여기서 transaction 사용 X
                $this->createOrder($data, $memberId);
            }
            return [
                'status' => true,
                'message' => '상품셋 정보를 성공적으로 업데이트했습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '엑셀 파일로부터 상품 정보들을 추출하는 과정에서 에러가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    private function getRowData($sheet, $row)
    {
        return [
            'productCode' => $sheet->getCell('A' . $row)->getValue(),
            'quantity' => $sheet->getCell('B' . $row)->getValue(),
            'receiverName' => $sheet->getCell('C' . $row)->getValue(),
            'receiverPhone' => $sheet->getCell('D' . $row)->getValue(),
            'receiverAddress' => $sheet->getCell('E' . $row)->getValue(),
            'receiverRemark' => $sheet->getCell('F' . $row)->getValue()
        ];
    }
    private function createOrder($data, $memberId)
    {
        try {
            $product = DB::table('minewing_products')
                ->where('productCode', $data['productCode'])
                ->where('isActive', 'Y')
                ->first();
            if (!$product) return [
                'status' => false,
                'message' => '품절되었거나 존재하지 않는 상품입니다.'
            ];
            //w주무냉성시작 cart랑 order랑 wingTransaction생성
            $cart = $this->storeCart($memberId, $product->id, $data['quantity']);
            $cartId = $cart['data']['cartId'];
            $amount = $this->getCartAmount($cartId);
            $wingTransaction = $this->storeWingTransaction($memberId, 'PAYMENT', $amount, "");
            $wingTransactionId = $wingTransaction['data']['wingTransactionId'];
            $priceThen = $this->getSalePrice($product->id);
            $this->storeOrder($wingTransactionId, $cartId, $data['receiverName'], $data['receiverPhone'], $data['receiverAddress'], $data['receiverRemark'], $priceThen, $product->shipping_fee, $product->bundle_quantity, $orderDate = null);
            return [
                'status' => true,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'data' => $data['productCode'],
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
    // Wing 거래 저장
    private function storeWingTransaction($memberId, $type, $amount, $remark)
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
    // 카트 금액 계산
    private function getCartAmount($cartId)
    {
        $cart = DB::table('carts AS c')
            ->join('minewing_products AS mp', 'mp.id', '=', 'c.product_id')
            ->where('c.id', $cartId)
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

        $margin = DB::table('sellwing_config')->where('id', 2)->value('value');
        $marginRate = ($margin / 100) + 1;

        return ceil($productPrice * $marginRate);
    }
    private function validateColumns($rowData)
    {
        foreach ($rowData as $key => $value) {
            if (empty($value)) {
                return [
                    'status' => false,
                    'message' => '모든 열에 값이 있어야 합니다. 비어 있는 값이 있습니다.',
                    'data' => $rowData['productCode'] ?? 'N/A'
                ];
            }
        }
        $productCode = $this->validateProductCode($rowData['productCode']);
        if ($productCode === false) {
            return [
                'status' => false,
                'message' => '유효하지 않은 상품코드입니다.',
                'data' => $rowData['productCode']
            ];
        }
        $product = $this->validateIsActive($rowData['productCode']);
        if ($product === false) {
            return [
                'status' => false,
                'message' => '품절처리된 상품입니다.',
                'data' => $rowData['productCode']
            ];
        }
        $quantity = $this->validateQuantity($rowData['quantity']);
        if ($quantity === false) {
            return [
                'status' => false,
                'message' => '1개 이상의 상품을 주문해주세요.',
                'data' => $rowData['productCode']
            ];
        }
        return [
            'status' => true,
            'data' => $rowData
        ];
    }
    private function validateIsActive($productCode)
    {
        $isActive = DB::table('minewing_products')
            ->where('productCode', $productCode)
            ->select('isActive');
        if ($isActive === 'N') return false;
        return true;
    }
    private function validateProductCode($productCode)
    {
        return DB::table('minewing_products')
            ->where('productCode', $productCode)
            ->exists();
    }
    private function validateQuantity($quantity)
    {
        if ($quantity < 1) return false;
        return true;
    }
}
