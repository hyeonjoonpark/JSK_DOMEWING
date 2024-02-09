<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Product\NameController;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TestController extends Controller
{
    public function index()
    {
        $spreadsheet = IOFactory::load(public_path('assets/excel/product_name_edit.xlsx'));
        $sheet = $spreadsheet->getSheet(0);
        $fails = [];
        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // 빈 셀도 반복
            $cells = [];
            foreach ($cellIterator as $cell) {
                $cells[] = $cell->getValue();
            }
            // A열(상품코드)과 B열(상품명) 추출
            $productCode = $cells[0]; // A열 값
            $productName = $cells[1]; // B열 값
            // 상품코드와 상품명이 모두 있는 경우만 배열에 추가
            if (!empty($productCode) && !empty($productName)) {
                $response = $this->updateProductName($productCode, $productName);
                if ($response['status'] === false) {
                    $fails[] = $productCode;
                }
            } else {
                $fails[] = $productCode;
            }
        }
        return $fails;
    }
    public function updateProductName($productCode, $productName)
    {
        try {
            DB::table('minewing_products')
                ->where('productCode', $productCode)
                ->update([
                    'productName' => $productName
                ]);
            return [
                'status' => true,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage(),
            ];
        }
    }
}
