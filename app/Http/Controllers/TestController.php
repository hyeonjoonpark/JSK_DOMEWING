<?php
namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TestController extends Controller
{
    public function index()
    {
        $spreadsheet = IOFactory::load(public_path('assets/excel/unique_product_name.xlsx'));
        $worksheet = $spreadsheet->getActiveSheet();

        $highestRow = $worksheet->getHighestRow(); // 총 행 수

        for ($row = 1; $row <= $highestRow; ++$row) {
            $cellValue = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
            DB::table('existed_product_name')->insert([
                'productName' => $cellValue
            ]);
        }
    }
}