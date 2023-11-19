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
        $spreadsheet = IOFactory::load(public_path('assets/excel/domeggook_codes.xlsx'));
        $worksheet = $spreadsheet->getSheet(3);

        $highestRow = $worksheet->getHighestRow(); // 총 행 수

        for ($row = 2; $row <= $highestRow; ++$row) {
            $category = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
            $code = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
            DB::table('domeggook_category')->insert([
                'code' => $code,
                'category' => $category
            ]);
        }
    }
}