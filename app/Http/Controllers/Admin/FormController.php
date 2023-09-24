<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FormController extends Controller
{
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
                'descImage' => '<p align="center"><img src="' . $descImage . '"></p>',
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