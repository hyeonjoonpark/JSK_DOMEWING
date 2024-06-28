<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Admin\ProductDataValidityController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Minewing\SaveController;
use App\Http\Controllers\Partners\Products\UploadedController;
use App\Http\Controllers\Product\NameController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelUploadController extends Controller
{
    private $productDataValidityController;
    private $nameController;
    public function __construct()
    {
        $this->productDataValidityController = new ProductDataValidityController();
        $this->nameController = new NameController();
    }
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
                $this->storeOrder($product); //주문 넣기 시작
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
    private function storeOrder($product)
    {
        try {
            $productName = $this->nameController->index($product['productName']);
            $sc = new SaveController();
            $sc->insertMappingwing($product['categoryID']);
            DB::table('minewing_products')
                ->where('productCode', $product['productCode'])
                ->update([
                    'categoryID' => $product['categoryID'],
                    'productName' => $productName,
                    'productKeywords' => $product['productKeywords'],
                    'productPrice' => $product['productPrice'],
                    'shipping_fee' => $product['shipping_fee'],
                    'bundle_quantity' => $product['bundle_quantity']
                ]);
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
