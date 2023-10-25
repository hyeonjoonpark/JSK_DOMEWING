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
        $excelPath = public_path('assets/excel/wholesaledepot.xls');
        $data = $this->readExcelData($excelPath);

        foreach ($data as $row) {
            $piTitle = $row['title'];
            $piValue = $row['value'];

            $this->updateProductInformation($piTitle, $piValue);
        }
    }

    private function readExcelData($excelPath)
    {
        $spreadsheet = IOFactory::load($excelPath);
        $sheet = $spreadsheet->getSheet(3);

        $data = [];
        $i = 0;
        foreach ($sheet->getRowIterator() as $row) {
            if ($i != 0) {
                $cellIterator = $row->getCellIterator();
                $titleCell = $cellIterator->current();
                $title = $titleCell->getValue();

                $cellIterator->next(); // 다음 셀로 이동
                $valueCell = $cellIterator->current();
                $value = $valueCell->getValue();

                $data[] = [
                    'title' => $title,
                    'value' => $value,
                ];
            }
            $i++;
        }

        return $data;
    }

    private function updateProductInformation($piTitle, $piValue)
    {
        try {
            DB::table('product_information')
                ->where('content', 'LIKE', "%$piTitle%")
                ->update(['wsd_value' => $piValue]);
            echo $piTitle . $piValue;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}