<?php

namespace App\Http\Controllers\ProductEditor;

use App\Http\Controllers\Admin\ProductDataValidityController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\DownloadController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MainController extends Controller
{
    private $productDataValidityController;
    public function __construct()
    {
        $this->productDataValidityController = new ProductDataValidityController();
    }
    public function main(Request $request)
    {
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
            $isFirstRow = true;
            $columnMappings = [
                'A' => 'productCode',
                'B' => 'categoryID',
                'C' => 'productName',
                'D' => 'productKeywords',
                'E' => 'productPrice',
                'F' => 'productDetail',
                'G' => 'isActive'
            ];
            $errors = [];
            $products=[];
            foreach ($sheet->getRowIterator() as $row) {
                if ($isFirstRow) {
                    $isFirstRow = false;
                    continue;
                }
                $cellIterator = $row->getCellIterator('A', 'G');
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell) {
                    $columnLetter = $cell->getColumn();
                    if (isset($columnMappings[$columnLetter])) {
                        $value = $cell->getValue();
                        $rowData[$columnMappings[$columnLetter]] = $value;
                    }
                }
                $validateColumnsResult = $this->validateColumns($rowData);
                if ($validateColumnsResult['status'] === false) {
                    $productCode = $validateColumnsResult['return']['productCode'];
                    $error = $validateColumnsResult['return']['error'];
                    $errors[] = [
                        'productCode' => $productCode,
                        'error' => $error
                    ];
                }
                $products[]=$rowData;
            }
            if (count($errors) > 0) {
                return [
                    'status' => false,
                    'return' => '일부 상품에서 오류가 검출되었습니다.',
                    'errors' => $errors
                ];
            }
            foreach($)
            return [
                'status' => true,
                'return' => '상품셋 정보를 성공적으로 업데이트했습니다.',
                'errors' => $errors
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => '엑셀 파일로부터 상품 정보들을 추출하는 과정에서 에러가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    private function updateProduct($rowData)
    {
        try {
            DB::table('minewing_products')
                ->where('productCode', $rowData['productCode'])
                ->update([
                    'categoryID' => $rowData['categoryID'],
                    'productName' => $rowData['productName'],
                    'productKeywords' => $rowData['productKeywords'],
                    'productPrice' => $rowData['productPrice'],
                    'productDetail' => $rowData['productDetail'],
                    'isActive' => $rowData['isActive']
                ]);
            return [
                'status' => true
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => [
                    'productCode' => $rowData['productCode'],
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
        $isActive = $this->validateIsActive($rowData['isActive']);
        if ($isActive === false) {
            return [
                'status' => false,
                'return' => [
                    'productCode' => $rowData['productCode'],
                    'error' => '상품 상태는 Y 혹은 N 으로 진열 여부를 기입해주세요.'
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
