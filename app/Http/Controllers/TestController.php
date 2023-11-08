<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function handle()
    {
        $excel = IOFactory::load(public_path('assets/excel/domeatoz_product_information.xlsx'));
        $sheet = $excel->getSheet(0);
        $data = [];
        $cnt = 0;
        foreach ($sheet->getRowIterator() as $row) {
            if ($cnt != 0) {
                $cellIterator = $row->getCellIterator();
                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }
                $data[] = $rowData;
            }
            $cnt++;
        }
        foreach ($data as $row) {
            DB::table('product_information')->where('content', 'LIKE', '%' . $row[0] . '%')->update([
                'domeatoz_value' => $row[1]
            ]);
        }
    }
}