<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $validator = Validator::make($request->all(), [
            'orders' => 'required|file|mimes:xlsx'
        ], [
            'orders' => '.xlsx 확장자를 사용하는 올바른 엑셀 파일을 업로드해주세요.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'return' => $validator->errors()->first()
            ];
        }
        $ordersExcelFile = $request->orders;
        return $this->extractOrdersExcelFile($ordersExcelFile);
    }
    private function extractOrdersExcelFile($productsExcelFile)
    {
        try {
            $partnerId = Auth::guard('partner')->id(); //이거 파트너스 계정 확인
            return DB::table('partner_domewing_accounts')
                ->where('partner_id', $partnerId)
                ->where('is_active', 'Y')
                ->first();
            $spreadsheet = IOFactory::load($productsExcelFile->getRealPath());
            $sheet = $spreadsheet->getSheet(0);
            $errors = [];
            $products = [];
            $highestRow = $sheet->getHighestRow();
            if ($highestRow >= 501) {
                return [
                    'status' => false,
                    'message' => '데이터는 헤더를 제외하고 2번째부터 501번째 행까지, 총 500개의 행 이하이어야 합니다.'
                ];
            }
            for ($i = 2; $i <= $highestRow; $i++) {
                $rowData = [
                    'productCode' => $sheet->getCell('A' . $i)->getValue(),
                    'quantity' => $sheet->getCell('B' . $i)->getValue(),
                    'receiverName' => $sheet->getCell('C' . $i)->getValue(),
                    'receiverPhone' => $sheet->getCell('D' . $i)->getValue(),
                    'receiverAddress' => $sheet->getCell('E' . $i)->getValue(),
                    'receiverRemark' => $sheet->getCell('F' . $i)->getValue()
                ];
                $validateColumnsResult = $this->validateColumns($rowData);
                if ($validateColumnsResult['status'] === false) {
                    $productCode = $validateColumnsResult['return']['data'];
                    $message = $validateColumnsResult['return']['message'];
                    $errors[] = $productCode . $message;
                } else {
                    $products[] = $rowData;
                }
            }
            if (count($errors) > 0) {
                return [
                    'status' => false,
                    'message' => '일부 상품에서 오류가 검출되었습니다.',
                    'errors' => $errors
                ];
            }
            foreach ($products as $product) {
                $this->createOrder($product); //주문 넣기 시작
            }
            return [
                'status' => true,
                'return' => '상품셋 정보를 성공적으로 업데이트했습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => '엑셀 파일로부터 상품 정보들을 추출하는 과정에서 에러가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    private function createOrder($product)
    {
        try {
            //w주무냉성시작 cart랑 order랑 wingTransaction생성
            $this->storeCart($memberId, $productId, $quantity);
            $this->storeOrder($wingTransactionId, $cartId, $receiverName, $receiverPhone, $receiverAddress, $receiverRemark, $priceThen, $shippingFeeThen, $bundleQuantityThen, $orderDate = null);
            $this->storeWingTransaction($memberId, $type, $amount, $remark);
            return [
                'status' => true,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => [
                    'productCode' => $product['productCode'],
                    'error' => $e->getMessage()
                ]
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
    private function validateColumns($rowData)
    {
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
            'return' => $rowData
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
