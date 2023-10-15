<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CategoryMappingController extends Controller
{
    public function wsConvertCategoryCode($categoryCode)
    {
        $spreadsheet = IOFactory::load(public_path('assets/excel/wholesaledepot.xls'));
        $worksheet = $spreadsheet->getSheetByName('도매창고 분류코드표');
        $data = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }
            $data[] = $rowData;
        }
        $keyword = $this->wdGetKeyword($categoryCode);
        $code = 4;
        foreach ($data as $row) {
            if (in_array($keyword, $row)) {
                $code = $row[0];
            }
        }
        return $code;
    }
    public function wdGetKeyword($categoryCode)
    {
        $categoryString = DB::table('category')->where('id', $categoryCode)->select('wholeCategoryName')->first()->wholeCategoryName;
        $categories = explode(">", $categoryString);
        // 배열의 마지막 요소를 선택합니다.
        $lastCategory = trim(end($categories));
        return $lastCategory;
    }
}