<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CategoryMappingController extends Controller
{
    public function domeggookCategoryCode($code)
    {
        $categoryString = DB::table('category')->where('code', $code)->select('wholeCategoryName')->first()->wholeCategoryName;
        $categories = explode(">", $categoryString);
        // 배열의 마지막 요소를 선택합니다.
        $keyword = trim(end($categories));
        $spreadsheet = IOFactory::load(public_path('assets/excel/domeggook_codes.xlsx'));
        $worksheet = $spreadsheet->getSheet(3);
        $data = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }
            $data[] = $rowData;
        }
        $code = '5';
        foreach ($data as $row) {
            if (in_array($keyword, $row)) {
                $code = $row[0];
            }
        }
        return $code;
    }
    public function domeroCategoryCode($code)
    {
        $categoryString = DB::table('category')->where('code', $code)->select('wholeCategoryName')->first()->wholeCategoryName;
        $categories = explode(">", $categoryString);
        // 배열의 마지막 요소를 선택합니다.
        $keyword = trim(end($categories));
        $spreadsheet = IOFactory::load(public_path('assets/excel/domero.xls'));
        $worksheet = $spreadsheet->getSheet(1);
        $data = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }
            $data[] = $rowData;
        }
        $code = '3';
        foreach ($data as $row) {
            if (in_array($keyword, $row)) {
                $code = $row[0];
            }
        }
        return $code . $keyword;
    }
    public function domeatozCategoryCode($code)
    {
        $categoryString = DB::table('category')->where('code', $code)->select('wholeCategoryName')->first()->wholeCategoryName;
        $categories = explode(">", $categoryString);
        // 배열의 마지막 요소를 선택합니다.
        $keyword = trim(end($categories));
        $spreadsheet = IOFactory::load(public_path('assets/excel/domeatoz_category.xlsx'));
        $worksheet = $spreadsheet->getSheet(0);
        $data = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }
            $data[] = $rowData;
        }
        $code = '1001001001';
        foreach ($data as $row) {
            if (in_array($keyword, $row)) {
                $code = $row[4];
            }
        }
        return $code;
    }
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
        $categoryString = DB::table('category')->where('code', $categoryCode)->select('wholeCategoryName')->first()->wholeCategoryName;
        $categories = explode(">", $categoryString);
        // 배열의 마지막 요소를 선택합니다.
        $lastCategory = trim(end($categories));
        return $lastCategory;
    }
}
