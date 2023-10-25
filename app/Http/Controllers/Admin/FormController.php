<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use App\Http\Controllers\Admin\CategoryMappingController;

class FormController extends Controller
{
    public function wholesaledepot(Request $request, $username, $password, $categoryCode, $productImage, $descImage)
    {
        try {
            // 카테고리 코드 변환을 위한 컨트롤러 생성
            $categoryMappingController = new CategoryMappingController();
            $categoryCode = $categoryMappingController->wsConvertCategoryCode($categoryCode);

            // 엑셀 파일 불러오기
            $spreadsheet = IOFactory::load(public_path('assets/excel/wholesaledepot.xls'));
            $sheet = $spreadsheet->getSheet(0);

            // 배송비 설정
            if ($request->shipping != '선불') {
                $shipCost = 0;
            } else {
                $shipCost = $request->shipCost;
            }

            $taxability = ($request->taxability === '과세') ? 1 : 2;

            if ($request->shipping == '선불') {
                $shipping = 0;
            } elseif ($request->shipping == '착불') {
                $shipping = 3;
            } elseif ($request->shipping == '무료') {
                $shipping = 1;
            }

            $saleToMinor = ($request->saleToMinor == '가능') ? '2' : '1';

            $productInformationCode = DB::table('product_information')
                ->where('domesin_value', $request->product_information)
                ->select('wsd_value')
                ->first()
                ->wsd_value;

            // 제품 데이터 배열 생성
            $dataset = [
                'productName' => $request->itemName,
                'invoiceName' => $request->invoiceName,
                'categoryCode' => $categoryCode,
                'productCode' => '',
                'productCode2' => '',
                'originated' => $request->origin,
                'vendor' => $request->vendor,
                'brand' => $request->vendor,
                'taxability' => $taxability,
                'shipType' => $shipping,
                'bundledQuantity' => '0',
                'isInternationalShipping' => '0',
                'keywords' => $request->keywords,
                'productPrice' => $request->price,
                'forcedPrice' => '',
                'isForced' => '0',
                'isConsumerNotified' => '0',
                'descImage' => $descImage,
                'productImage' => $productImage,
                'extraImage1' => '',
                'extraImage2' => '',
                'extraImage3' => '',
                'extraImage4' => '',
                'extraImage5' => '',
                'isExclusive' => '0',
                'selectedOption' => '0',
                'inputOption' => '',
                'credentials' => 'C',
                'credentialCode' => '',
                'refundAddress' => '1597',
                'isRefundable' => '1',
                'refundReason' => '',
                'saleToMinor' => $saleToMinor,
                'isSale' => '0',
                'isDisplay' => '1',
                'isNew' => 'N',
                'productNotice' => '',
                'productInformationCode' => $productInformationCode,
                'productInformation0' => '상품 상세설명에 표시',
                'productInformation1' => '상품 상세설명에 표시',
                'productInformation2' => '상품 상세설명에 표시',
                'productInformation3' => '상품 상세설명에 표시',
                'productInformation4' => '상품 상세설명에 표시',
                'productInformation5' => '상품 상세설명에 표시',
                'productInformation6' => '상품 상세설명에 표시',
                'productInformation7' => '상품 상세설명에 표시',
                'productInformation8' => '상품 상세설명에 표시',
                'productInformation9' => '상품 상세설명에 표시',
                'productInformation10' => '상품 상세설명에 표시',
                'productInformation11' => '상품 상세설명에 표시',
                'productInformation12' => '상품 상세설명에 표시',
                'productInformation13' => '상품 상세설명에 표시',
                'productInformation14' => '상품 상세설명에 표시',
                'productInformation15' => '상품 상세설명에 표시',
                'productInformation16' => '상품 상세설명에 표시',
                'productInformation17' => '상품 상세설명에 표시',
                'productInformation18' => '상품 상세설명에 표시',
                'productInformation19' => '상품 상세설명에 표시',
                'productInformation20' => '상품 상세설명에 표시',
                'productInformation21' => '상품 상세설명에 표시',
            ];

            // 제품 정보를 엑셀에 추가
            $newRow = 5;
            $col = 'A';
            foreach ($dataset as $value) {
                $sheet->setCellValue($col . $newRow, $value);
                $col++;
            }

            // 엑셀 파일 업로드
            $writer = new Xls($spreadsheet);
            $fileName = 'wsd_' . $username . '_' . date('YmdHis') . '.xls';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer->save($formedExcelFile);

            // 결과 반환
            $data['status'] = 1;
            $data['return'] = $fileName;
            return $data;
        } catch (Exception $e) {
            // 오류가 발생한 경우 처리
            $data['status'] = -1;
            $data['return'] = $e->getMessage();
            return $data;
        }
    }

    public function domesin(Request $request, $username, $password, $categoryCode, $productImage, $descImage)
    {
        try {
            $spreadsheet = IOFactory::load(public_path('assets/excel/domesin.xls'));
            $sheet = $spreadsheet->getsheet(0);
            if ($request->shipping != '선불') {
                $shipCost = 0;
            } else {
                $shipCost = $request->shipCost;
            }
            // 데이터 배열 생성
            $domesinCode = DB::table('category')->where('code', $categoryCode)->select('domesinCode')->first();
            $domesinCode = $domesinCode->domesinCode;
            if ($request->taxability === '과세') {
                $taxability = 0;
            } else {
                $taxability = 1;
            }
            if ($request->shipping == '선불') {
                $shipping = 0;
            } else if ($request->shipping == '착불') {
                $shipping = 2;
            } else if ($request->shipping == '무료') {
                $shipping = 1;
            }
            if ($request->saleToMinor == '가능') {
                $saleToMinor = '';
            } else {
                $saleToMinor = '1';
            }
            $dataset = [
                'productCode' => '',
                'productName' => $request->itemName,
                'categoryCode' => $domesinCode,
                'invoiceName' => $request->invoiceName,
                'productCode2' => '',
                'originated' => $request->origin,
                'vendor' => $request->vendor,
                'brand' => $request->vendor,
                'model' => $request->model,
                'taxability' => $taxability,
                'shipType' => $shipping,
                'bundledQuantity' => '',
                'isInternationalShipping' => '',
                'shipCost' => $shipCost,
                'refundCost' => $shipCost,
                'refundAddress' => '1508',
                'keywords' => $request->keywords,
                'productPrice' => $request->price,
                'forcedPrice' => '',
                'normalPrice' => '',
                'descImage' => $descImage,
                'productImage' => $productImage,
                'extraImage1' => '',
                'extraImage2' => '',
                'extraImage3' => '',
                'extraImage4' => '',
                'extraImage5' => '',
                'isExclusive' => '0',
                'selectedOption' => '',
                'inputOption' => '',
                'credentials' => '0',
                'credentialCode' => '',
                'credentialNum' => '',
                'isRefundable' => '1',
                'saleToMinor' => $saleToMinor,
                'isSale' => '0',
                'isDisplay' => '1',
                'productCondition' => '',
                'productNotice' => '',
                'productInformationCode' => $request->product_information,
                'productInformation0' => '상품 상세설명에 표시',
                'productInformation1' => '상품 상세설명에 표시',
                'productInformation2' => '상품 상세설명에 표시',
                'productInformation3' => '상품 상세설명에 표시',
                'productInformation4' => '상품 상세설명에 표시',
                'productInformation5' => '상품 상세설명에 표시',
                'productInformation6' => '상품 상세설명에 표시',
                'productInformation7' => '상품 상세설명에 표시',
                'productInformation8' => '상품 상세설명에 표시',
                'productInformation9' => '상품 상세설명에 표시',
                'productInformation10' => '상품 상세설명에 표시',
                'productInformation11' => '상품 상세설명에 표시',
                'productInformation12' => '상품 상세설명에 표시',
                'productInformation13' => '상품 상세설명에 표시',
                'productInformation14' => '상품 상세설명에 표시',
                'productInformation15' => '상품 상세설명에 표시',
                'productInformation16' => '상품 상세설명에 표시',
                'productInformation17' => '상품 상세설명에 표시',
                'productInformation18' => '상품 상세설명에 표시',
                'productInformation19' => '상품 상세설명에 표시',
                'productInformation20' => '상품 상세설명에 표시',
                'productInformation21' => '상품 상세설명에 표시',
            ];
            // 추가할 새로운 행의 위치를 지정합니다.
            $newRow = 4; // 현재 데이터가 있는 가장 아래 행 다음에 추가하려면 +1을 사용합니다.
            $col = 'A';
            foreach ($dataset as $value) {
                $sheet->setCellValue($col . $newRow, $value);
                $col++;
            }
            // 엑셀 파일 업로드
            // 변경된 내용을 파일로 저장
            $writer = new Xls($spreadsheet);
            $fileName = $username . '_' . date('YmdHis') . '.xls';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer->save($formedExcelFile);
            $data['status'] = 1;
            $data['return'] = $fileName;
            return $data;
        } catch (Exception $e) {
            $data['status'] = -1;
            $data['return'] = $e->getMessage();
            return $data;
        }
    }
    public function ownerclan(Request $request, $username, $password, $categoryCode, $productImage, $descImage)
    {
        try {
            $spreadsheet = IOFactory::load(public_path('assets/excel/ownerclan.xlsx'));
            $sheet = $spreadsheet->getSheet(0);

            // Medical 값 설정
            $medical = 0;
            if ($request->madicalEquipment == '의료기기') {
                $medical = 1;
            } elseif ($request->healthFunctional == '건강기능식품') {
                $medical = 2;
            }
            $informationContents = '상품 상세설명 참조
            상품 상세설명 참조
            상품 상세설명 참조
            상품 상세설명 참조
            상품 상세설명 참조
            상품 상세설명 참조
            상품 상세정보에 별도 표기
            상품 상세정보에 별도 표기
            상품 상세정보에 별도 표기
            상품 상세정보에 별도 표기';

            // 각 줄의 앞에 있는 공백 제거
            $informationContents = preg_replace('/^\h+/m', '', $informationContents);
            if ($request->shipping != '선불') {
                $shipCost = 0;
            } else {
                $shipCost = $request->shipCost;
            }
            // 데이터 배열 생성
            $data = [
                'productCode' => '',
                'categoryCode' => $categoryCode,
                'categoryName' => '',
                'openmarketCategory' => '',
                'exclusiveCode' => '',
                'productName' => $request->itemName,
                'invoiceName' => $request->invoiceName,
                'keywords' => $request->keywords,
                'originated' => $request->origin,
                'vendor' => $request->vendor,
                'model' => $request->model,
                'productPrice' => $request->price,
                'salesType' => '자율',
                'customerPrice' => '',
                'taxability' => $request->taxability,
                'optionName' => '',
                'optionValue' => '',
                'optionPrice' => '',
                'managementInformation' => 'N',
                'productImage' => $productImage,
                'extraImages' => '',
                'descImage' => $descImage,
                'saleToMinor' => $request->saleToMinor,
                'shipType' => $request->shipping,
                'shipCost' => $shipCost,
                'refundCost' => $shipCost,
                'bundledQuantity' => '',
                'refundBlockReason' => '',
                'refundAddress' => '',
                'refundType' => '1',
                'credentials' => '0',
                'documents' => '',
                'informationType' => '35',
                'informationContents' => $informationContents,
                'productAttribute' => $medical,
                'shipAddress' => '',
                'productRemark' => '',
                'productIsActive' => '',
                'createdAt' => '',
                'updatedAt' => '',
            ];
            // 추가할 새로운 행의 위치를 지정합니다.
            $newRow = $sheet->getHighestRow() + 1; // 현재 데이터가 있는 가장 아래 행 다음에 추가하려면 +1을 사용합니다.
            $col = 'A';
            foreach ($data as $value) {
                $sheet->setCellValue($col . $newRow, $value);
                $col++;
            }
            // 엑셀 파일 업로드
            // 변경된 내용을 파일로 저장
            $writer = new Xlsx($spreadsheet);
            $fileName = $username . '_' . date('YmdHis') . '.xlsx';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer->save($formedExcelFile);
            $data['status'] = 1;
            $data['return'] = $fileName;
            return $data;
        } catch (Exception $e) {
            $data['status'] = -1;
            $data['return'] = $e->getMessage();
            return $data;
        }
    }
}