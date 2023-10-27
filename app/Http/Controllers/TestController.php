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
        $excel = IOFactory::load(public_path('assets/excel/domeggook_codes.xlsx'));
        $sheet = $excel->getSheet(4);
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
            if ($row[1] != '') {
                DB::table('product_information')->where('content', 'LIKE', '%' . $row[1] . '%')->update([
                    'domeggook_value' => $row[0]
                ]);
            }
        }
    }
}
