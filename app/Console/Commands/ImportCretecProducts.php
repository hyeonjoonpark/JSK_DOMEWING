<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ImportCretecProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-cretec-products';

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
        ini_set('memory_limit', '-1');
        $sheet = $this->loadSheet();
        $this->extractSheetData($sheet);
    }
    private function loadSheet()
    {
        $excelPath = storage_path('app/public/excel/cretec_products.csv');
        $spreadsheet = IOFactory::load($excelPath);
        $sheet = $spreadsheet->getActiveSheet();
        return $sheet;
    }
    private function extractSheetData($sheet)
    {
        foreach ($sheet->getRowIterator() as $index => $row) {
            if ($index > 10) {
                break;
            }
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            print_r($cellIterator);
            // foreach ($cellIterator as $cell) {

            //     $product = [
            //         'sellerID' => 61,
            //         'userID' => 15,
            //         'productCode' => $this->createProductCode(),
            //         'productName'=>
            //     ];
            // }
        }
    }
    private function createProductCode()
    {
        do {
            $productCode = Str::random(8);
            $exists = DB::table('minewing_products')
                ->where('productCode', $productCode)
                ->exists();
        } while ($exists === true);
        return $productCode;
    }
}
