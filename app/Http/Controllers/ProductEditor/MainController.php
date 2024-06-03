<?php

namespace App\Http\Controllers\ProductEditor;

use App\Http\Controllers\Admin\ProductDataValidityController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Minewing\SaveController;
use App\Http\Controllers\Product\NameController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MainController extends Controller
{
    private $productDataValidityController;
    private $nameController;
    public function __construct()
    {
        $this->productDataValidityController = new ProductDataValidityController();
        $this->nameController = new NameController();
    }
    public function main(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_allow', '-1');
        $validator = Validator::make($request->all(), [
            'products' => 'required|file|mimes:xlsx'
        ], [
            'products' => '.xlsx 확장자를 사용하는 올바른 엑셀 파일을 업로드해주세요.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'return' => $validator->errors()->first()
            ];
        }
        $productsExcelFile = $request->products;
        return $this->extractProductsExcelFile($productsExcelFile);
    }
    private function extractProductsExcelFile($productsExcelFile)
    {
        try {
            $spreadsheet = IOFactory::load($productsExcelFile->getRealPath());
            $sheet = $spreadsheet->getSheet(0);
            $errors = [];
            $products = [];
            $highestRow = $sheet->getHighestRow();
            if ($highestRow >= 5002) {
                return [
                    'status' => false,
                    'message' => '데이터는 헤더를 제외하고 2번째부터 5001번째 행까지, 총 5000개의 행 이하이어야 합니다.'
                ];
            }
            for ($i = 2; $i <= $highestRow; $i++) {
                $rowData = [
                    'productCode' => $sheet->getCell('A' . $i)->getValue(),
                    'categoryID' => $sheet->getCell('B' . $i)->getValue(),
                    'productName' => $this->nameController->index($sheet->getCell('C' . $i)->getValue()),
                    'productKeywords' => $this->productDataValidityController->validateKeywords($sheet->getCell('D' . $i)->getValue()),
                    'productPrice' => $sheet->getCell('E' . $i)->getValue(),
                    'shipping_fee' => $sheet->getCell('F' . $i)->getValue(),
                    'bundle_quantity' => $sheet->getCell('G' . $i)->getValue(),
                ];
                $validator = Validator::make($rowData, [
                    'productCode' => ['required', 'exists:minewing_products,productCode'],
                    'categoryID' => ['required', 'integer', 'exists:ownerclan_category,id'],
                    'productName' => ['required'],
                    'productKeywords' => [],
                    'productPrice' => ['required', 'integer', 'min:10'],
                    'shipping_fee' => ['required', 'integer', 'min:10'],
                    'bundle_quantity' => ['required', 'integer']
                ], [
                    'productCode.required' => '상품 코드는 필수 항목입니다.',
                    'productCode.exists' => '존재하지 않는 상품 코드입니다.',
                    'categoryID.required' => '카테고리 ID는 필수 항목입니다.',
                    'categoryID.integer' => '카테고리 ID는 정수여야 합니다.',
                    'categoryID.exists' => '존재하지 않는 카테고리 ID입니다.',
                    'productName.required' => '상품 이름은 필수 항목입니다.',
                    'productPrice.required' => '상품 가격은 필수 항목입니다.',
                    'productPrice.integer' => '상품 가격은 정수여야 합니다.',
                    'productPrice.min' => '상품 가격은 최소 10원이어야 합니다.',
                    'shipping_fee.required' => '배송비는 필수 항목입니다.',
                    'shipping_fee.integer' => '배송비는 정수여야 합니다.',
                    'shipping_fee.min' => '배송비는 최소 10원이어야 합니다.',
                    'bundle_quantity.required' => '번들 수량은 필수 항목입니다.',
                    'bundle_quantity.integer' => '번들 수량은 정수여야 합니다.'
                ]);
                $validateColumnsResult = $this->validateColumns($rowData);
                if ($validateColumnsResult['status'] === false) {
                    $productCode = $validateColumnsResult['return']['productCode'];
                    $error = $validateColumnsResult['return']['error'];
                    $errors[] = [
                        'productCode' => $productCode,
                        'error' => $error
                    ];
                } else {
                    $products[] = $validateColumnsResult['return'];
                }
            }
            if (count($errors) > 0) {
                return [
                    'status' => false,
                    'return' => '일부 상품에서 오류가 검출되었습니다.',
                    'errors' => $errors
                ];
            }
            $productCodes = [];
            foreach ($products as $product) {
                $this->updateProduct($product);
                $productCodes[] = $product['productCode'];
            }
            $productCodes = join(',', $productCodes);
            return [
                'status' => true,
                'return' => '상품셋 정보를 성공적으로 업데이트했습니다.',
                'errors' => $errors,
                'productCodes' => $productCodes,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => '엑셀 파일로부터 상품 정보들을 추출하는 과정에서 에러가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    private function updateProduct($product)
    {
        try {
            $productName = $this->nameController->index($product['productName']);
            $sc = new SaveController();
            $sc->insertMappingwing($product['categoryID']);
            DB::table('minewing_products')
                ->where('productCode', $product['productCode'])
                ->update([
                    'categoryID' => $product['categoryID'],
                    // 'productName' => $productName,
                    'productKeywords' => $product['productKeywords'],
                    // 'productPrice' => $product['productPrice'],
                    // 'productDetail' => $product['productDetail']
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
                'return' => [
                    'productCode' => $rowData['productCode'],
                    'error' => '유효한 상품 코드가 아닙니다.'
                ]
            ];
        }
        $categoryId = $this->validateCategoryId($rowData['categoryID']);
        if ($categoryId === false) {
            return [
                'status' => false,
                'return' => [
                    'productCode' => $rowData['productCode'],
                    'error' => '유효한 카테고리 코드가 아닙니다.'
                ]
            ];
        }
        $productKeywords = $this->productDataValidityController->validateKeywords($rowData['productKeywords']);
        if ($productKeywords !== true) {
            return [
                'status' => false,
                'return' => [
                    'productCode' => $rowData['productCode'],
                    'error' => $productKeywords
                ]
            ];
        }
        $productPrice = $this->validateProductPrice($rowData['productPrice']);
        if ($productPrice === false) {
            return [
                'status' => false,
                'return' => [
                    'productCode' => $rowData['productCode'],
                    'error' => '상품 가격은 0보다 큰 정수여야 합니다.'
                ]
            ];
        }
        return [
            'status' => true,
            'return' => $rowData
        ];
    }
    private function validateIsActive($isActive)
    {
        if ($isActive === 'Y' || $isActive === 'N') {
            return true;
        }
        return false;
    }
    private function validateProductPrice($productPrice)
    {
        try {
            $productPrice = intval($productPrice);
        } catch (\Exception $e) {
            return false;
        }
        if ($productPrice > 0) {
            return true;
        }
        return false;
    }
    private function validateCategoryId($categoryId)
    {
        return DB::table('ownerclan_category')
            ->where('id', $categoryId)
            ->exists();
    }
    private function validateProductCode($productCode)
    {
        return DB::table('minewing_products')
            ->where('productCode', $productCode)
            ->exists();
    }
}
