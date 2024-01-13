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
                    'code' => $row[1],
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

            $startRow = 5206; // Begin processing from this row (skip rows before this)
            $rows = [];

            foreach ($worksheet->getRowIterator($startRow) as $row) {
                $rowIndex = $row->getRowIndex();

                // Extract values from specific columns
                $cellAValue = $worksheet->getCell('A' . $rowIndex)->getValue();
                $cellGValue = $worksheet->getCell('G' . $rowIndex)->getValue();
                $cells = [$cellAValue, $cellGValue];

                // Add to rows array and insert into database
                $rows[] = $cells;
                $this->insertDB($cells, $b2BEngName);
            }

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
