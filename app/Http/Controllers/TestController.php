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
        $spreadsheet = IOFactory::load(public_path('assets/excel/domeatoz_category.xlsx'));
        $worksheet = $spreadsheet->getSheet(0);

        $highestRow = $worksheet->getHighestRow(); // 총 행 수

        for ($row = 2; $row <= $highestRow; ++$row) {
            $code = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
            $lg = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
            $md = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
            $sm = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
            $xs = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
            DB::table('domeatoz_category')->insert([
                'code' => $code,
                'lg' => $lg,
                'md' => $md,
                'sm' => $sm,
                'xs' => $xs,
            ]);
        }
    }
}
