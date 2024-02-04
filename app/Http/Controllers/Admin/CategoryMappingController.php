<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CategoryMappingController extends Controller
{
    public function domeggookCategoryCode($categoryId)
    {
        $categoryString = DB::table('category')
            ->where('id', $categoryId)
            ->value('wholeCategoryName');

        if (!$categoryString) {
            $rand = rand(1, 3565);
            return DB::table('domeggook_category')->where('id', $rand)->value('code'); // 수정됨
        }

        $keywords = array_reverse(explode(">", $categoryString));

        foreach ($keywords as $keyword) {
            $trimmedKeyword = trim($keyword);
            $categoryCode = DB::table('domeggook_category')
                ->where('category', 'LIKE', '%' . $trimmedKeyword . '%')
                ->value('code');

            if ($categoryCode) {
                return $categoryCode;
            }
        }

        $rand = rand(1, 3565);
        return DB::table('domeggook_category')->where('id', $rand)->value('code'); // 수정됨
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
    public function domeatozCategoryCode($categoryId)
    {
        $categoryString = DB::table('category')
            ->where('id', $categoryId)
            ->value('wholeCategoryName');

        if (!$categoryString) {
            $rand = rand(676, 6930);
            return DB::table('domeatoz_category')->where('id', $rand)->value('code'); // 수정됨
        }

        $keywords = array_reverse(explode(">", $categoryString));

        foreach ($keywords as $keyword) {
            $trimmedKeyword = trim($keyword);
            foreach (['lg', 'md', 'sm', 'xs'] as $type) {
                $categoryCode = DB::table('domeatoz_category')
                    ->where($type, 'LIKE', '%' . $trimmedKeyword . '%')
                    ->value('code');

                if ($categoryCode) {
                    return $categoryCode;
                }
            }
        }

        $rand = rand(676, 6930);
        return DB::table('domeatoz_category')->where('id', $rand)->value('code'); // 수정됨
    }
    public function wsConvertCategoryCode($categoryId)
    {
        $categoryString = DB::table('category')
            ->where('id', $categoryId)
            ->value('wholeCategoryName');

        if (!$categoryString) {
            $rand = rand(1, 4666);
            return DB::table('wholesaledepot_category')->where('id', $rand)->value('code'); // 수정됨
        }

        $keywords = array_reverse(explode(">", $categoryString));

        foreach ($keywords as $keyword) {
            $trimmedKeyword = trim($keyword);
            foreach (['lg', 'md', 'sm', 'xs'] as $type) {
                $categoryCode = DB::table('wholesaledepot_category')
                    ->where($type, 'LIKE', '%' . $trimmedKeyword . '%')
                    ->value('code');

                if ($categoryCode) {
                    return $categoryCode;
                }
            }
        }

        $rand = rand(1, 4666);
        return DB::table('wholesaledepot_category')->where('id', $rand)->value('code'); // 수정됨
        // $spreadsheet = IOFactory::load(public_path('assets/excel/wholesaledepot.xls'));
        // $worksheet = $spreadsheet->getSheetByName('도매창고 분류코드표');
        // $data = [];
        // foreach ($worksheet->getRowIterator() as $row) {
        //     $cellIterator = $row->getCellIterator();
        //     $rowData = [];
        //     foreach ($cellIterator as $cell) {
        //         $rowData[] = $cell->getValue();
        //     }
        //     $data[] = $rowData;
        // }
        // $keyword = $this->wdGetKeyword($categoryCode);
        // $code = 4;
        // foreach ($data as $row) {
        //     if (in_array($keyword, $row)) {
        //         $code = $row[0];
        //     }
        // }
        // return $code;
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
