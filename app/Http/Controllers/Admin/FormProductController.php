<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Illuminate\Support\Facades\DB;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class FormProductController extends Controller
{
    private function getShippingFee($productId)
    {
        return DB::table('minewing_products AS mp')
            ->join('product_search AS ps', 'mp.sellerID', '=', 'ps.vendor_id')
            ->where('mp.id', $productId)
            ->first(['shipping_fee', 'additional_shipping_fee']);
    }
    public function funn($products, $margin_rate, $vendorEngName, $shippingCost, $index)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/funn.xls'));

            $firstSheet = $spreadsheet->getSheet(0);
            $firstSheet->setTitle('기본정보');
            $secondSheet = $spreadsheet->getSheet(1);
            $fifthSheet = $spreadsheet->getSheet(4);

            // 데이터 추가를 위한 행 인덱스 초기화
            $rowIndexFirstSheet = 2;
            $rowIndexSecondSheet = 2;
            $rowIndexFifthSheet = 2;

            // 데이터 추가
            foreach ($products as $product) {
                $getShippingFeeResult = $this->getShippingFee($product->id);
                $shippingCost = $getShippingFeeResult->shipping_fee;
                $additionalShippingFee = $getShippingFeeResult->additional_shipping_fee;
                $tobizonCategoryID = $product->categoryID;
                $categoryCode = $this->getCategoryCode($vendorEngName, $tobizonCategoryID);
                $marginedPrice = (int)ceil($product->productPrice * $margin_rate);

                $serialNumber = $rowIndexFirstSheet - 1;

                $dataForFirstSheet = [
                    $serialNumber,
                    $product->productCode,
                    '국내',
                    $product->productName,
                    '',
                    '',
                    $product->productKeywords,
                    '',
                    '',
                    '',
                    $categoryCode,
                    '제한없음',
                    '일반상품',
                    '',
                    99999,
                    1,
                    '과세',
                    '가격자율',
                    $marginedPrice,
                    '',
                    '',
                    '',
                    '선불',
                    $shippingCost,
                    '가능',
                    $shippingCost,
                    $shippingCost * 2,
                    6624,
                    'JS협력사',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '기타',
                ];
                $dataForSecondSheet = [
                    $serialNumber,
                    $product->productImage,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $product->productDetail
                ];
                $dataForFifthSheet = [
                    $serialNumber,
                    35
                ];
                // 엑셀에 데이터 추가
                $colIndex = 1;
                foreach ($dataForFirstSheet  as $value) {
                    $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex) . $rowIndexFirstSheet;
                    $firstSheet->setCellValue($cellCoordinate, $value);
                    $colIndex++;
                }
                $rowIndexFirstSheet++;

                $colIndex = 1;
                foreach ($dataForSecondSheet as $value) {
                    $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex) . $rowIndexSecondSheet;
                    $secondSheet->setCellValue($cellCoordinate, $value);
                    $colIndex++;
                }
                $rowIndexSecondSheet++;

                $colIndex = 1;
                foreach ($dataForFifthSheet as $value) {
                    $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex) . $rowIndexFifthSheet;
                    $fifthSheet->setCellValue($cellCoordinate, $value);
                    $colIndex++;
                }
                $rowIndexFifthSheet++;
            }
            // 엑셀 파일 저장
            $fileName = 'funn_' . now()->format('YmdHis') . '_' . $index . '.xls';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $spreadsheet->setActiveSheetIndex(0);
            $writer = new Xls($spreadsheet);
            $writer->save($formedExcelFile);
            $downloadURL = asset('assets/excel/formed/' . $fileName);
            return ['status' => true, 'return' => $downloadURL];
        } catch (\Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage()
            ];
        }
    }
    public function onch3($products, $margin_rate, $vendorEngName, $shippingCost, $index)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/onch3.xlsx'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 4;
            foreach ($products as $product) {
                $getShippingFeeResult = $this->getShippingFee($product->id);
                $shippingCost = $getShippingFeeResult->shipping_fee;
                $additionalShippingFee = $getShippingFeeResult->additional_shipping_fee;
                $ownerclanCategoryID = $product->categoryID;
                $categoryCode = $this->getCategoryCode($vendorEngName, $ownerclanCategoryID);
                $marginedPrice = ceil(($product->productPrice * $margin_rate) / 10) * 10;
                $productKeywords = str_replace(',', '/', $product->productKeywords);
                $customerMarginRate = 1.45;
                $customerPrice = ceil(($marginedPrice * $customerMarginRate) / 10) * 10;
                $data = [
                    $categoryCode,
                    $product->productName,
                    '본 상품 선택',
                    $customerPrice,
                    '',
                    $marginedPrice,
                    1,
                    'N',
                    3,
                    $shippingCost,
                    '오후 1시/본사/당일출고',
                    $additionalShippingFee,
                    $additionalShippingFee,
                    'N',
                    0,
                    '왕복반품배송비: ' . $shippingCost * 2 . ', 공급사수거접수',
                    2,
                    1,
                    '',
                    $productKeywords,
                    $product->productDetail,
                    $product->productImage,
                    '',
                    '',
                    '',
                    $product->productCode,
                    26,
                    '해당사항없음',
                    '해당사항없음',
                    '해당사항없음',
                    0,
                    'JS협력사',
                    '기타',
                    'JS협력사/기타'
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
            $fileName = 'onch3_' . now()->format('YmdHis') . '_' . $index . '.xlsx';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xlsx($spreadsheet);
            $writer->save($formedExcelFile);
            $downloadURL = asset('assets/excel/formed/' . $fileName);
            return ['status' => true, 'return' => $downloadURL];
        } catch (\Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage()
            ];
        }
    }

    public function kseller($products, $margin_rate, $vendorEngName, $shippingCost, $index)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/kseller.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 4;
            foreach ($products as $product) {
                $getShippingFeeResult = $this->getShippingFee($product->id);
                $shippingCost = $getShippingFeeResult->shipping_fee;
                $additionalShippingFee = $getShippingFeeResult->additional_shipping_fee;
                $tobizonCategoryID = $product->categoryID;
                $categoryCode = $this->getCategoryCode($vendorEngName, $tobizonCategoryID);
                $basicShippingFee = 3000;
                $gappedShippingFee = $shippingCost - $basicShippingFee;
                $marginedPrice = (int)ceil($product->productPrice * $margin_rate) + $gappedShippingFee;


                $data = [
                    $categoryCode,
                    $product->productName,
                    '',
                    $product->productCode,
                    '기타',
                    'JS협력사',
                    '',
                    0,
                    0,
                    '',
                    'N',
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
                    '',
                    '',
                    '',
                    0,
                    '',
                    '',
                    0,
                    '',
                    0,
                    1,
                    '',
                    '',
                    35,
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
            $fileName = 'kseller_' . now()->format('YmdHis') . '_' . $index . '.xls';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xls($spreadsheet);
            $writer->save($formedExcelFile);
            $downloadURL = asset('assets/excel/formed/' . $fileName);
            return ['status' => true, 'return' => $downloadURL];
        } catch (\Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage()
            ];
        }
    }
    public function tobizon($products, $margin_rate, $vendorEngName, $shippingCost, $index)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/tobizon.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 4;
            foreach ($products as $product) {
                $getShippingFeeResult = $this->getShippingFee($product->id);
                $shippingCost = $getShippingFeeResult->shipping_fee;
                $additionalShippingFee = $getShippingFeeResult->additional_shipping_fee;
                $tobizonCategoryID = $product->categoryID;
                $categoryCode = $this->getCategoryCode($vendorEngName, $tobizonCategoryID);
                $marginedPrice = (int)ceil($product->productPrice * $margin_rate);
                $data = [
                    $categoryCode,
                    $product->productName,
                    '',
                    $product->productCode,
                    '',
                    'Y',
                    'JS협력사',
                    '기타',
                    '',
                    '',
                    $product->productKeywords,
                    $marginedPrice,
                    '',
                    'N',
                    'N',
                    'FE',
                    0,
                    'S',
                    $shippingCost,
                    $shippingCost,
                    $shippingCost * 2,
                    $shippingCost + $additionalShippingFee,
                    $shippingCost + $additionalShippingFee,
                    'Y',
                    'N',
                    'N',
                    '',
                    '',
                    'N',
                    '',
                    $product->productImage,
                    '',
                    '',
                    '',
                    '',
                    'S',
                    'N',
                    'B',
                    'N',
                    'N',
                    'C',
                    '',
                    $product->productDetail,
                    '',
                    35,
                    '상세설명 참조',
                    '상세설명 참조',
                    '상세설명 참조',
                    '상세설명 참조',
                    '상세설명 참조',
                    '상세설명 참조',
                    '상세설명 참조',
                    '상세설명 참조'
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
            $fileName = 'tobizon_' . now()->format('YmdHis') . '_' . $index . '.xls';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xls($spreadsheet);
            $writer->save($formedExcelFile);
            $downloadURL = asset('assets/excel/formed/' . $fileName);
            return ['status' => true, 'return' => $downloadURL];
        } catch (\Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage()
            ];
        }
    }
    public function specialoffer($products, $margin_rate, $vendorEngName, $shippingCost, $index)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/specialoffer.xlsx'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 6;
            foreach ($products as $product) {
                $getShippingFeeResult = $this->getShippingFee($product->id);
                $shippingCost = $getShippingFeeResult->shipping_fee;
                $additionalShippingFee = $getShippingFeeResult->additional_shipping_fee;
                $ownerclanCategoryID = $product->categoryID;
                $categoryCode = $this->getCategoryCode($vendorEngName, $ownerclanCategoryID);
                $marginedPrice = (int)ceil($product->productPrice * $margin_rate);
                $data = [
                    '',
                    $categoryCode,
                    $product->productName,
                    $product->productKeywords,
                    'JS협력사',
                    '',
                    '기타',
                    'JS협력사',
                    '13:00',
                    1,
                    1,
                    '',
                    '',
                    0,
                    $marginedPrice,
                    '',
                    '',
                    0,
                    0,
                    '',
                    '',
                    3,
                    0,
                    $shippingCost,
                    '',
                    $product->productImage,
                    $product->productImage,
                    '',
                    '',
                    '',
                    '',
                    $product->productDetail,
                    '',
                    0,
                    '',
                    35,
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
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    'N',
                    'N',
                    1,
                    '',
                    'N',
                    $product->productCode,
                    '',
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
            $fileName = 'specialoffer_' . now()->format('YmdHis') . '_' . $index . '.xlsx';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xlsx($spreadsheet);
            $writer->save($formedExcelFile);
            $downloadURL = asset('assets/excel/formed/' . $fileName);
            return ['status' => true, 'return' => $downloadURL];
        } catch (\Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage()
            ];
        }
    }
    public function sellingkok($products, $margin_rate, $vendorEngName, $shippingCost, $index)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/sellingkok.xlsx'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 3;
            foreach ($products as $product) {
                $getShippingFeeResult = $this->getShippingFee($product->id);
                $shippingCost = $getShippingFeeResult->shipping_fee;
                $additionalShippingFee = $getShippingFeeResult->additional_shipping_fee;
                $ownerclanCategoryID = $product->categoryID;
                $categoryCode = $this->getCategoryCode($vendorEngName, $ownerclanCategoryID);
                $marginedPrice = (int)ceil($product->productPrice * $margin_rate);
                $data = [
                    '',
                    'Y',
                    $product->productName,
                    $product->productName,
                    $product->productKeywords,
                    $categoryCode,
                    '상세설명표시',
                    'JS협력사',
                    'JS협력사',
                    '',
                    'N',
                    'N',
                    0,
                    $product->productCode,
                    'N',
                    $product->productImage,
                    '',
                    $product->productDetail,
                    '',
                    40,
                    '',
                    $marginedPrice,
                    '',
                    'N',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '과세',
                    '택배',
                    '',
                    0,
                    '선결제:기본배송비',
                    $shippingCost,
                    'Y',
                    'A1705389919',
                    'A1705389919',
                    $shippingCost,
                    'Y',
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
            $fileName = 'sellingkok_' . now()->format('YmdHis') . '_' . $index . '.xlsx';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xlsx($spreadsheet);
            $writer->save($formedExcelFile);
            $downloadURL = asset('assets/excel/formed/' . $fileName);
            return ['status' => true, 'return' => $downloadURL];
        } catch (\Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage()
            ];
        }
    }
    public function domero($products, $margin_rate, $vendorEngName, $shippingCost, $index)
    {
        $fixedShippingCost = 3000;
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/domero.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 4;
            foreach ($products as $product) {
                $getShippingFeeResult = $this->getShippingFee($product->id);
                $shippingCost = $getShippingFeeResult->shipping_fee;
                $additionalShippingFee = $getShippingFeeResult->additional_shipping_fee;
                $ownerclanCategoryID = $product->categoryID;
                $categoryCode = $this->getCategoryCode($vendorEngName, $ownerclanCategoryID);
                $marginedPrice = (int)ceil($product->productPrice * $margin_rate) + (int)($shippingCost - $fixedShippingCost);
                $data = [
                    $categoryCode,
                    $product->productName,
                    $product->productName,
                    $product->productCode,
                    '기타',
                    'JS협력사',
                    'JS협력사',
                    0,
                    0,
                    '',
                    '',
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
                    '',
                    '',
                    '',
                    0,
                    '',
                    '',
                    0,
                    '',
                    0,
                    1,
                    '',
                    '',
                    35,
                    '상세정보별도표기',
                    '상세정보별도표기',
                    '상세정보별도표기',
                    '상세정보별도표기',
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
            $fileName = 'domero_' . now()->format('YmdHis') . '_' . $index . '.xls';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xls($spreadsheet);
            $writer->save($formedExcelFile);
            $downloadURL = asset('assets/excel/formed/' . $fileName);
            return ['status' => true, 'return' => $downloadURL];
        } catch (Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage(),
            ];
        }
    }
    public function domeggook($products, $margin_rate, $vendorEngName, $shippingCost, $index)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/domeggook.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 2;
            $minAmount = 5000;
            foreach ($products as $product) {
                $getShippingFeeResult = $this->getShippingFee($product->id);
                $shippingCost = $getShippingFeeResult->shipping_fee;
                $additionalShippingFee = $getShippingFeeResult->additional_shipping_fee;
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
                    'JS협력사',
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
            $fileName = 'domeggook_' . now()->format('YmdHis') . '_' . $index . '.xls';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xls($spreadsheet);
            $writer->save($formedExcelFile);
            $downloadURL = asset('assets/excel/formed/' . $fileName);
            return ['status' => true, 'return' => $downloadURL];
        } catch (Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage(),
            ];
        }
    }
    public function domeggook2($products, $margin_rate, $vendorEngName, $shippingCost, $index)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/domeggook.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 2;
            $minAmount = 5000;
            foreach ($products as $product) {
                $getShippingFeeResult = $this->getShippingFee($product->id);
                $shippingCost = $getShippingFeeResult->shipping_fee;
                $additionalShippingFee = $getShippingFeeResult->additional_shipping_fee;
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
                    'JS협력사',
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
            $fileName = 'domeggook_' . now()->format('YmdHis') . '_' . $index . '.xls';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xls($spreadsheet);
            $writer->save($formedExcelFile);
            $downloadURL = asset('assets/excel/formed/' . $fileName);
            return ['status' => true, 'return' => $downloadURL];
        } catch (Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage(),
            ];
        }
    }
    public function domeatoz($products, $margin_rate, $vendorEngName, $shippingCost, $index)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/domeatoz.xlsx'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 3;
            foreach ($products as $product) {
                $getShippingFeeResult = $this->getShippingFee($product->id);
                $shippingCost = $getShippingFeeResult->shipping_fee;
                $additionalShippingFee = $getShippingFeeResult->additional_shipping_fee;
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
                    'JS협력사',
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
            $fileName = 'domeatoz_' . now()->format('YmdHis') . '_' . $index . '.xlsx';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xlsx($spreadsheet);
            $writer->save($formedExcelFile);
            $downloadURL = asset('assets/excel/formed/' . $fileName);
            return ['status' => true, 'return' => $downloadURL];
        } catch (Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage(),
            ];
        }
    }
    public function wholesaledepot($products, $margin_rate, $vendorEngName, $shippingCost, $index)
    {
        try {
            $fixedShippingCost = 2500;
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/wholesaledepot.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 5;
            foreach ($products as $product) {
                $getShippingFeeResult = $this->getShippingFee($product->id);
                $shippingCost = $getShippingFeeResult->shipping_fee;
                $additionalShippingFee = $getShippingFeeResult->additional_shipping_fee;
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
                    'JS협력사',
                    'JS협력사',
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
            $fileName = 'wholesaledepot_' . now()->format('YmdHis') . '_' . $index . '.xls';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xls($spreadsheet);
            $writer->save($formedExcelFile);
            $downloadURL = asset('assets/excel/formed/' . $fileName);
            return ['status' => true, 'return' => $downloadURL];
        } catch (Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage(),
            ];
        }
    }

    public function domesin($products, $margin_rate, $vendorEngName, $shippingCost, $index)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/domesin.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 4;
            foreach ($products as $product) {
                $getShippingFeeResult = $this->getShippingFee($product->id);
                $shippingCost = $getShippingFeeResult->shipping_fee;
                $additionalShippingFee = $getShippingFeeResult->additional_shipping_fee;
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
                    'JS협력사',
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
            $fileName = 'domesin_' . now()->format('YmdHis') . '_' . $index . '.xls';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xls($spreadsheet);
            $writer->save($formedExcelFile);
            $downloadURL = asset('assets/excel/formed/' . $fileName);
            return ['status' => true, 'return' => $downloadURL];
        } catch (Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage(),
            ];
        }
    }
    public function ownerclan($products, $margin_rate, $vendorEngName, $shippingCost, $index)
    {
        try {
            $startRowIndex = 4;
            $detailedDescription = str_repeat("상품 상세설명 참조\n", 6) . str_repeat("상품 상세정보에 별도 표기\n", 4);

            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/ownerclan.xlsx'));
            $sheet = $spreadsheet->getSheet(0);

            // 데이터 추가
            $rowIndex = $startRowIndex;
            foreach ($products as $product) {
                $getShippingFeeResult = $this->getShippingFee($product->id);
                $shippingCost = $getShippingFeeResult->shipping_fee;
                $additionalShippingFee = $getShippingFeeResult->additional_shipping_fee;
                $ownerclanCategoryID = $product->categoryID;
                $categoryCode = $this->getCategoryCode($vendorEngName, $ownerclanCategoryID);
                $marginedPrice = (int)ceil($product->productPrice * $margin_rate);
                $data = [
                    '', $categoryCode, '', '', '', $product->productName, $product->productName,
                    $product->productKeywords, '기타', "JS협력사", '', $marginedPrice, '자율', '',
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
            $fileName = 'ownerclan_' . now()->format('YmdHis') . '_' . $index . '.xlsx';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xlsx($spreadsheet);
            $writer->save($formedExcelFile);
            $downloadURL = asset('assets/excel/formed/' . $fileName);
            return ['status' => true, 'return' => $downloadURL];
        } catch (Exception $e) {
            return ['status' => false, 'return' => $e->getMessage()];
        }
    }
    public function getCategoryCode($vendorEngName, $ownerclanCategoryID)
    {
        $tableName = $vendorEngName . '_category';
        try {
            $categoryCode = DB::table('category_mapping AS cm')
                ->join($tableName, $tableName . '.id', '=', 'cm.' . $vendorEngName)
                ->where('cm.ownerclan', $ownerclanCategoryID)
                ->select($tableName . '.code')
                ->first()
                ->code;
            return $categoryCode;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
