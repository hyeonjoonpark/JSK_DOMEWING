<?php

namespace App\Console\Commands;

use App\Http\Controllers\Minewing\SaveController;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
            if ($index === 0) {
                continue;
            }
            if ($index > 10) {
                break;
            }
            $product = $this->createProduct($sheet, $index + 1);
        }
    }
    private function createProduct($sheet, $excelIndex)
    {
        $productName = $this->createProductName($sheet, $excelIndex);
        echo $productName;
        $saveController = new SaveController();
        $productCode = $saveController->generateRandomProductCode(8);
        $product = [
            'sellerID' => 61,
            'userID' => 15,
            'productCode' => $productCode,
            'productName' => $productName
        ];
    }
    private function createProductName($sheet, $excelIndex)
    {
        $brandName = $sheet->getCell('F' . $excelIndex)->getValue();
        $basicName = $sheet->getCell('G' . $excelIndex)->getValue();
        $modelName = $sheet->getCell('H' . $excelIndex)->getValue();
        $productName = $brandName . ' ' . $basicName . ' ' . $modelName;
        echo $productName;
        return $productName;
    }
}
