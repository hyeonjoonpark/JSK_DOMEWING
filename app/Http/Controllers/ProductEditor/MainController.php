<?php

namespace App\Http\Controllers\ProductEditor;

use App\Http\Controllers\Admin\ProductDataValidityController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Minewing\SaveController;
use App\Http\Controllers\Partners\Products\UploadedController;
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
                    'productCode' => trim($sheet->getCell('A' . $i)->getValue()),
                    'categoryID' => trim($sheet->getCell('B' . $i)->getValue()),
                    'productName' => trim($sheet->getCell('C' . $i)->getValue()),
                    'productKeywords' => trim($sheet->getCell('D' . $i)->getValue()),
                    'productPrice' => trim($sheet->getCell('E' . $i)->getValue()),
                    'shipping_fee' => trim($sheet->getCell('F' . $i)->getValue()),
                    'bundle_quantity' => trim($sheet->getCell('G' . $i)->getValue()),
                ];
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
            // 파트너스 업로드된 상품들에 반영.
            $uc = new UploadedController();
            $partnerErrors = $uc->fetchEdittedProducts($products);
            // END.
            $productCodes = [];
            foreach ($products as $product) {
                $this->updateProduct($product);
                $productCodes[] = $product['productCode'];
            }
            $productCodesStr = join(',', $productCodes);
            return [
                'status' => true,
                'return' => '상품셋 정보를 성공적으로 업데이트했습니다.',
                'errors' => $errors,
                'productCodes' => $productCodesStr,
                'partnerErrors' => $partnerErrors
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
        $shippingFee = $this->validateProductPrice($rowData['shipping_fee']);
        $bundleQuantity = $this->validateProductPrice($rowData['bundle_quantity']);
        if ($shippingFee < 10) {
            return [
                'status' => false,
                'return' => [
                    'productCode' => $rowData['productCode'],
                    'error' => '배송비는 10원보다 커야 합니다.'
                ]
            ];
        }
        if ($bundleQuantity < 0) {
            return [
                'status' => false,
                'return' => [
                    'productCode' => $rowData['productCode'],
                    'error' => '묶음 배송 수량은 0보다 크거나 같아야 합니다.'
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
