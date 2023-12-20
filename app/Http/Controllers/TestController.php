<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TestController extends Controller
{
    public function index()
    {
        $filePath = public_path('assets/excel/domesin_category.xls');
        $reader = IOFactory::createReaderForFile($filePath);
        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $isFirstRow = true; // Flag to skip the first row

        foreach ($worksheet->getRowIterator() as $row) {
            // Skip the first row (header)
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $cells = [];
            foreach ($cellIterator as $cell) {
                $cells[] = $cell->getValue();
            }

            $categoryCode = $cells[0]; // Assuming '분류코드' is in the first column
            $category = $cells[1];     // Assuming '카테고리' is in the second column

            // Insert into database
            DB::table('domesin_category')->insert([
                'code' => $categoryCode,
                'name' => $category
            ]);
        }
    }
}
