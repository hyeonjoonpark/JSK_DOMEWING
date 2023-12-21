<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Illuminate\Support\Facades\DB;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class FormProductController extends Controller
{
    public function index()
    {
        $data = [];
        $fromUPID = 1;
        $products = DB::table('minewing_products')
            ->where('isActive', 'Y')
            ->limit(500)
            ->get();
        $vendors = DB::table('product_register')
            ->join('vendors', 'vendors.id', '=', 'product_register.vendor_id')
            ->where('vendors.is_active', 'ACTIVE')
            ->where('product_register.is_active', 'Y')
            ->get();
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        foreach ($vendors as $vendor) {
            $vendorEngName = $vendor->name_eng;
            $margin_rate = DB::table('margin_rate')
                ->where('vendorID', $vendor->id)
                ->first()
                ->rate;
            $margin_rate = (100 + $margin_rate) / 100;
            $response = $this->$vendorEngName($products, $margin_rate, $vendor->id);
            if ($response['status'] == 1) {
                $data['return']['successVendors'][] = $vendor->name;
                $data['return']['successVendorsNameEng'][] = $vendorEngName;
                $data['return']['formedExcelFiles'][] = $response['return'];
            }
        }
        return $data;
    }
    public function domeggook($products, $margin_rate, $vendorEngName)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/domeggook.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 2;
            $shippingCost = 3500;
            $minAmount = 5000;
            foreach ($products as $product) {
                $ownerclanCategoryID = $product->categoryID;
                $categoryCode = $this->getCategoryCode($vendorEngName, $ownerclanCategoryID);
                $marginedPrice = (int)ceil($product->productPrice * $margin_rate);
                $minQuantity = ceil($minAmount / $marginedPrice);
                $data = [
                    '',
                    '도매꾹,도매매',
                    '직접판매',
                    'N',
                    $product->productName,
                    $product->productKeywords,
                    $categoryCode,
                    '상세정보별도표기',
                    '',
                    'LADAM',
                    'N',
                    'N',
                    '1X1X1',
                    '1',
                    $product->productCode,
                    $product->productImage,
                    $product->productDetail,
                    '',
                    '',
                    '',
                    'Y',
                    '',
                    40,
                    '전체상세정보별도표시',
                    '전체상세정보별도표시',
                    'N',
                    $minQuantity . ':' . $marginedPrice,
                    '',
                    'N',
                    'N',
                    '1:' . $marginedPrice,
                    '',
                    '',
                    'N',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '99999',
                    '과세',
                    '',
                    '택배',
                    'Y',
                    '',
                    0,
                    '선결제:고정배송비',
                    $shippingCost,
                    '선결제:고정배송비',
                    $shippingCost,
                    '',
                    'SA0058243',
                    $shippingCost,
                    'N',
                    365,
                    'Y'
                ];
                // 엑셀에 데이터 추가
                $colIndex = 1;
                foreach ($data as $value) {
                    $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
                    $sheet->setCellValue($cellCoordinate, $value);
                    $colIndex++;
                }
                $rowIndex++;
            }
            // 엑셀 파일 저장
            $fileName = 'domeggook_' . now()->format('YmdHis') . '.xls';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xls($spreadsheet);
            $writer->save($formedExcelFile);
            $downloadURL = "https://www.sellwing.kr/assets/excel/formed/" . $fileName;
            return ['status' => true, 'return' => $downloadURL];
        } catch (Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage(),
            ];
        }
    }
    public function domeatoz($products, $margin_rate, $vendorEngName)
    {
        try {
            $shippingCost = 3500;
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/domeatoz.xlsx'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 3;
            foreach ($products as $product) {
                $ownerclanCategoryID = $product->categoryID;
                $categoryCode = $this->getCategoryCode($vendorEngName, $ownerclanCategoryID);
                $marginedPrice = (int)ceil($product->productPrice * $margin_rate);
                $data = [
                    $categoryCode,
                    '',
                    '',
                    $product->productName,
                    '',
                    $product->productName,
                    $product->productKeywords,
                    '',
                    $marginedPrice,
                    '',
                    0,
                    '배송비선불',
                    '',
                    $shippingCost,
                    $shippingCost,
                    5000,
                    5000,
                    'LADAM',
                    '기타',
                    '',
                    $product->productImage,
                    768,
                    'Y',
                    $product->productDetail,
                    '',
                    '',
                    0,
                    '',
                    '',
                    $product->productCode,
                    '',
                    35,
                    '상세정보별도표기',
                    '상세정보별도표기',
                    '상세정보별도표기',
                    '상세정보별도표기',
                    '상세정보별도표기',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '상세정보별도표기',
                    '상세정보별도표기',
                    '상세정보별도표기',
                    '상세정보별도표기'
                ];
                // 엑셀에 데이터 추가
                $colIndex = 1;
                foreach ($data as $value) {
                    $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
                    $sheet->setCellValue($cellCoordinate, $value);
                    $colIndex++;
                }
                $rowIndex++;
            }
            // 엑셀 파일 저장
            $fileName = 'domeatoz_' . now()->format('YmdHis') . '.xlsx';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xlsx($spreadsheet);
            $writer->save($formedExcelFile);
            $downloadURL = "https://www.sellwing.kr/assets/excel/formed/" . $fileName;
            return ['status' => true, 'return' => $downloadURL];
        } catch (Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage(),
            ];
        }
    }
    public function wholesaledepot($products, $margin_rate, $vendorEngName)
    {
        try {
            $shippingCost = 3500;
            $fixedShippingCost = 2500;
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/wholesaledepot.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 5;
            foreach ($products as $product) {
                $ownerclanCategoryID = $product->categoryID;
                $categoryCode = $this->getCategoryCode($vendorEngName, $ownerclanCategoryID);
                $marginedPrice = (int)ceil($product->productPrice * $margin_rate) + (int)($shippingCost - $fixedShippingCost);
                $data = [
                    $product->productName,
                    $product->productName,
                    $categoryCode,
                    '',
                    $product->productCode,
                    '기타',
                    'LADAM',
                    'LADAM',
                    1,
                    0,
                    '',
                    0,
                    $product->productKeywords,
                    $marginedPrice,
                    '',
                    0,
                    0,
                    $product->productDetail,
                    $product->productImage,
                    '',
                    '',
                    '',
                    '',
                    '',
                    0,
                    0,
                    '',
                    'C',
                    '',
                    1597,
                    1,
                    '',
                    2,
                    0,
                    1,
                    'N',
                    '',
                    35,
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시'
                ];
                // 엑셀에 데이터 추가
                $colIndex = 1;
                foreach ($data as $value) {
                    $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
                    $sheet->setCellValue($cellCoordinate, $value);
                    $colIndex++;
                }
                $rowIndex++;
            }
            // 엑셀 파일 저장
            $fileName = 'wholesaledepot_' . now()->format('YmdHis') . '.xls';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xls($spreadsheet);
            $writer->save($formedExcelFile);
            $downloadURL = "https://www.sellwing.kr/assets/excel/formed/" . $fileName;
            return ['status' => true, 'return' => $downloadURL];
        } catch (Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage(),
            ];
        }
    }

    public function domesin($products, $margin_rate, $vendorEngName)
    {
        try {
            $shippingCost = 3500;
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/domesin.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 4;
            foreach ($products as $product) {
                $ownerclanCategoryID = $product->categoryID;
                $categoryCode = $this->getCategoryCode($vendorEngName, $ownerclanCategoryID);
                $marginedPrice = (int)ceil($product->productPrice * $margin_rate);
                $data = [
                    '',
                    $product->productName,
                    $categoryCode,
                    $product->productName,
                    $product->productCode,
                    '기타',
                    'LADAM',
                    '',
                    '',
                    0,
                    0,
                    '',
                    'N',
                    $shippingCost,
                    $shippingCost,
                    '1508',
                    $product->productKeywords,
                    $marginedPrice,
                    '',
                    '',
                    $product->productDetail,
                    $product->productImage,
                    '',
                    '',
                    '',
                    '',
                    '',
                    0,
                    '',
                    '',
                    0,
                    '',
                    '',
                    1,
                    '',
                    0,
                    1,
                    '',
                    '',
                    35,
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    ''
                ];
                // 엑셀에 데이터 추가
                $colIndex = 1;
                foreach ($data as $value) {
                    $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
                    $sheet->setCellValue($cellCoordinate, $value);
                    $colIndex++;
                }
                $rowIndex++;
            }
            // 엑셀 파일 저장
            $fileName = 'domesin_' . now()->format('YmdHis') . '.xls';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xls($spreadsheet);
            $writer->save($formedExcelFile);
            $downloadURL = "https://www.sellwing.kr/assets/excel/formed/" . $fileName;
            return ['status' => true, 'return' => $downloadURL];
        } catch (Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage(),
            ];
        }
    }
    public function ownerclan($products, $margin_rate, $vendorEngName)
    {
        try {
            $startRowIndex = 4;
            $shippingCost = 3500;
            $detailedDescription = str_repeat("상품 상세설명 참조\n", 6) . str_repeat("상품 상세정보에 별도 표기\n", 4);

            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/ownerclan.xlsx'));
            $sheet = $spreadsheet->getSheet(0);

            // 데이터 추가
            $rowIndex = $startRowIndex;
            foreach ($products as $product) {
                $ownerclanCategoryID = $product->categoryID;
                $categoryCode = $this->getCategoryCode($vendorEngName, $ownerclanCategoryID);
                $marginedPrice = (int)ceil($product->productPrice * $margin_rate);
                $data = [
                    '', $categoryCode, '', '', '', $product->productName, $product->productName,
                    $product->productKeywords, '기타', "LADAM", '', $marginedPrice, '자율', '',
                    '과세', '', '', '', "N," . $product->productCode, $product->productImage, '',
                    $product->productDetail, '가능', '선불', $shippingCost, $shippingCost, '', '', '', 1, 0, '',
                    35, $detailedDescription, 0, '', '', '', '', ''
                ];

                $colIndex = 1;
                foreach ($data as $value) {
                    $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
                    $sheet->setCellValue($cellCoordinate, $value);
                    $colIndex++;
                }
                $rowIndex++;
            }

            // 엑셀 파일 저장
            $fileName = 'ownerclan_' . now()->format('YmdHis') . '.xlsx';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xlsx($spreadsheet);
            $writer->save($formedExcelFile);
            $downloadURL = "https://www.sellwing.kr/assets/excel/formed/" . $fileName;
            return ['status' => true, 'return' => $downloadURL];
        } catch (Exception $e) {
            return ['status' => false, 'return' => $e->getMessage()];
        }
    }
    public function getCategoryCode($vendorEngName, $ownerclanCategoryID)
    {
        $tableName = $vendorEngName . '_category';
        $categoryCode = DB::table('category_mapping AS cm')
            ->join($tableName, $tableName . '.id', '=', 'cm.' . $vendorEngName)
            ->where('cm.ownerclan', $ownerclanCategoryID)
            ->select('code')
            ->first()
            ->code;
        return $categoryCode;
    }
}
