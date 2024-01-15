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
        $b2BEngName = "sellingkok";
        $ext = 'xlsx';
        $excel = public_path("assets/excel/{$b2BEngName}.{$ext}");
        $response = $this->extractExcel($excel, $b2BEngName);
        return $response;
    }

    public function insertDB($row, $b2BEngName)
    {
        try {
            DB::table("{$b2BEngName}_category")->insert([
                'code' => $row[0],
                'lg' => $row[1],
                'md' => $row[2],
                'sm' => $row[3],
                'xs' => $row[4],
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

            $startRow = 2; // Begin processing from this row (skip rows before this)
            $rows = [];

            foreach ($worksheet->getRowIterator($startRow) as $row) {
                $rowIndex = $row->getRowIndex();

                // Extract values from specific columns
                $columns = range('A', 'E');
                $cells = array_map(
                    fn ($column) => $worksheet->getCell("{$column}{$rowIndex}")->getValue(),
                    $columns
                );

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
