<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CreateCategoryExcelFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-category-excel-file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $categories = DB::table('ownerclan_category')->get(['id', 'name']);
        $filePath = public_path('assets/excel/sellwing_products_editor.xlsx');
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheet(1);
        $rowIndex = 2;
        foreach ($categories as $category) {
            $data = [
                $category->id,
                $category->name
            ];
            $colIndex = 1;
            foreach ($data as $value) {
                $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
                $sheet->setCellValue($cellCoordinate, $value);
                $colIndex++;
            }
            $rowIndex++;
        }
        $writer = new Xlsx($spreadsheet);
        return $writer->save($filePath);
    }
}
