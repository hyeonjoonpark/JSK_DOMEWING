<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Str;

class DownloadController extends Controller
{
    public function main(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productCodes' => 'required|array|min:1'
        ], [
            'productCodes' => '최소 1개 이상의 상품을 선택해주세요.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'return' => $validator->errors()->first()
            ];
        }
        $filePath = public_path('assets/excel/sellwing_products_editor.xlsx');
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheet(0);
        $rowIndex = 2;
        $productCodes = $request->productCodes;
        $products = DB::table('minewing_products')
            ->whereIn('productCode', $productCodes)
            ->get(['productCode', 'categoryID', 'productName', 'productKeywords', 'productPrice', 'productDetail', 'isActive']);
        foreach ($products as $product) {
            $data = [
                $product->productCode,
                $product->categoryID,
                $product->productName,
                $product->productKeywords,
                $product->productPrice,
                $product->productDetail,
                $product->isActive
            ];
            $colIndex = 1;
            foreach ($data as $value) {
                $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
                $sheet->setCellValue($cellCoordinate, $value);
                $colIndex++;
            }
            $rowIndex++;
        }
        $newFileName = Str::uuid() . '.xlsx';
        $newFilePath = public_path('assets/excel/sellwing-products-editor/') . $newFileName;
        $writer = new Xlsx($spreadsheet);
        $writer->save($newFilePath); // 새 파일 경로로 저장
        $downloadUrl = asset('assets/excel/sellwing-products-editor/' . $newFileName);
        return [
            'status' => true,
            'return' => $downloadUrl
        ];
    }
}
