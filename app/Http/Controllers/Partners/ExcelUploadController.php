<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use App\Http\Controllers\WingController;
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
        $orderDataResult = $this->extractOrdersExcelFile($ordersExcelFile);
        if ($orderDataResult['status'] === false) {
            return $orderDataResult;
        }
        $orderDatas = $orderDataResult['data'];
        $balanceCheck = $this->verifyBalance($memberId, $orderDatas);
        if ($balanceCheck['status'] === false) {
            return $balanceCheck;
        }
        $response = $this->createOrder($orderDatas, $memberId);
        if ($response) {
            return [
                'status' => true,
                'message' => '성공적으로 주문하였습니다.',
                'data' => $response
            ];
        }
        return [
            'status' => false,
            'message' => '알 수 없는 오류가 발생하였습니다. 관리자에게 문의 바랍니다.'
        ];
    }
    private function extractOrdersExcelFile($ordersExcelFile)
    {
        try {
            $spreadsheet = IOFactory::load($ordersExcelFile->getRealPath());
            $sheet = $spreadsheet->getSheet(0)->toArray();
            $highestRow = 0;
            foreach ($sheet as $row) {
                $filteredRow = array_filter($row, function ($cell) {
                    return $cell !== null && $cell !== '';
                });
                if (!empty($filteredRow)) {
                    $highestRow++;
                }
            }
            $errors = [];
            $datas = [];
            if ($highestRow >= 501) {
                return [
                    'status' => false,
                    'message' => '데이터는 헤더를 제외하고 2번째부터 501번째 행까지, 총 500개의 행 이하이어야 합니다.'
                ];
            }
            for ($i = 1; $i < $highestRow; $i++) {
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
            return [
                'status' => true,
                'data' => $datas
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '엑셀 파일로부터 상품 정보들을 추출하는 과정에서 에러가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    private function verifyBalance($memberId, $orderDatas)
    {
        $wingController = new WingController();
        $currentBalance = $wingController->getBalance($memberId);
        $totalOrderCost = 0;
        foreach ($orderDatas as $orderData) {
            $totalOrderCost += $this->getOrderAmount($orderData['productCode'], $orderData['quantity']);
            if ($currentBalance < $totalOrderCost) {
                return [
                    'status' => false,
                    'message' => '잔액이 주문 총 금액보다 부족합니다. 잔액 충전 후 다시 업로드 해주세요.'
                ];
            }
        }
        return [
            'status' => true
        ];
    }
    private function getRowData($sheet, $i)
    {
        return [
            'productCode' => $sheet[$i][0],
            'quantity' => $sheet[$i][1],
            'receiverName' => $sheet[$i][2],
            'receiverPhone' => $sheet[$i][3],
            'receiverAddress' => $sheet[$i][4],
            'receiverRemark' => $sheet[$i][5]
        ];
    }
    private function createOrder($orderData, $memberId)
    {
        try {
            foreach ($orderData as $data) {
                $product = DB::table('minewing_products')
                    ->where('productCode', $data['productCode'])
                    ->first();
                if (!$product) {
                    return [
                        'status' => false,
                        'message' => '품절되었거나 존재하지 않는 상품입니다.'
                    ];
                }
                $cart = $this->storeCart($memberId, $product->id, $data['quantity']);
                $cartId = $cart['data']['cartId'];
                $amount = $this->getCartAmount($cartId);
                $wingTransaction = $this->storeWingTransaction($memberId, 'PAYMENT', $amount, "");
                $wingTransactionId = $wingTransaction['data']['wingTransactionId'];
                $priceThen = $this->getSalePrice($product->id);
                $this->storeOrder($wingTransactionId, $cartId, $data['receiverName'], $data['receiverPhone'], $data['receiverAddress'], $data['receiverRemark'], $priceThen, $product->shipping_fee, $product->bundle_quantity, $orderDate = null);
            }
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
                'admin_remark' => '엑셀윙 주문'
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
    private function getOrderAmount($productCode, $quantity)
    {
        $product = DB::table('minewing_products')->where('productCode', $productCode)->first();
        $salePrice = $this->getSalePrice($product->id);
        $shippingRate = $product->bundle_quantity === 0 ? 1 : ceil($quantity / $product->bundle_quantity);
        return $salePrice * $quantity + $product->shipping_fee * $shippingRate;
    }
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
            if ($key !== 'receiverRemark' && (is_null($value) || trim($value) === '')) {
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
        $quantityValid = $this->validateQuantity($rowData['quantity']);
        if ($quantityValid === false) {
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
            ->value('isActive');
        return $isActive === 'Y';
    }
    private function validateProductCode($productCode)
    {
        return DB::table('minewing_products')
            ->where('productCode', $productCode)
            ->exists();
    }
    private function validateQuantity($quantity)
    {
        return $quantity >= 1;
    }
}
