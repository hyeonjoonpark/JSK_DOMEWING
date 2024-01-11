<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class InsertCategoryController extends Controller
{
    public function index()
    {
        set_time_limit(0);
        $b2BEngName = "domero";
        $ext = 'xls';
        $excel = public_path('assets/excel/' . $b2BEngName . '.' . $ext);
        $response = $this->extractExcel($excel, $b2BEngName);
        return $response;
    }
    public function insertDB($row, $b2BEngName)
    {
        try {
            DB::table($b2BEngName . '_category')
                ->insert([
                    'code' => $row[0],
                    'name' => $row[1]
                ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function extractExcel($excel, $b2BEngName)
    {
        try {
            $spreadsheet = IOFactory::load($excel);
            $worksheet = $spreadsheet->getSheet(1);
            $rows = [];
            $index = 0;
            foreach ($worksheet->getRowIterator() as $row) {
                if ($index == 0) {
                    $index++;
                    continue;
                }
                $rowIndex = $row->getRowIndex();
                // 각 행에서 첫 번째(A)와 두 번째(B) 셀의 값을 가져오기
                $cells = [];
                $cells[] = $worksheet->getCell('A' . $rowIndex)->getValue();
                $cells[] = $worksheet->getCell('G' . $rowIndex)->getValue();

                $rows[] = $cells;
                $this->insertDB($cells, $b2BEngName);
            }
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
