<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateDatasetController extends Controller
{
    public function index()
    {
        $tableName = 'ownerclan_category';
        $excelPath = public_path('assets/excel/ownerclan_category.xlsx');
        $extractedRows = $this->extractExcel($excelPath);
        foreach ($extractedRows as $row) {
            // 첫 번째 행(헤더) 건너뛰기
            if ($row === $extractedRows[0]) {
                continue;
            }
            $this->insertDB($tableName, $row);
        }
    }
    public function extractExcel($excelPath)
    {
        $reader = new Xlsx();
        $spreadsheet = $reader->load($excelPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        return $rows;
    }
    public function insertDB($tableName, $row)
    {
        try {
            $categoryCode = $row[0]; // 첫 번째 열이 카테고리 코드
            $categoryName = $row[1]; // 두 번째 열이 카테고리 이름
            DB::insert('INSERT INTO ' . $tableName . ' (code, name)
            VALUES (?, ?)', [$categoryCode, $categoryName]);
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
