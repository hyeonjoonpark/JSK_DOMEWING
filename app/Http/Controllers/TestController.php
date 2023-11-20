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
        $spreadsheet = IOFactory::load(public_path('assets/excel/wholesaledepot.xls'));
        $worksheet = $spreadsheet->getSheet(1);

        $highestRow = $worksheet->getHighestRow(); // 총 행 수

        for ($row = 2; $row <= $highestRow; ++$row) {
            $code = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
            $lg = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
            $md = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
            $sm = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
            $xs = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
            DB::table('wholesaledepot_category')->insert([
                'code' => $code,
                'lg' => $lg,
                'md' => $md,
                'sm' => $sm,
                'xs' => $xs,
            ]);
        }
    }
}
