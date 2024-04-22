<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelwingCretec extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:excelwing-cretec';

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
        $cretecProducts = $this->getCretecProducts();
        $sheet = $this->loadProductEditSheet();
        echo $this->writeProducts($cretecProducts, $sheet);
    }
    private function getCretecProducts()
    {
        return DB::table('minewing_products')
            ->where('sellerID', 61)
            ->get(['productCode', 'categoryID', 'productName', 'productKeywords', 'productPrice', 'productDetail', 'productHref'])
            ->toArray();
    }
    private function loadProductEditSheet()
    {
        $filePath = public_path('assets/excel/sellwing_products_editor.xlsx');
        $spreadsheet = IOFactory::load($filePath);
        return $spreadsheet->getSheet(0);
    }
    private function writeProducts($products, $sheet)
    {
        $spreadsheet = $sheet->getParent();
        $productChunks = array_chunk($products, 1000);
        foreach ($productChunks as $index => $products) {
            $rowIndex = 2;
            foreach ($products as $product) {
                $data = [
                    $product->productCode,
                    $product->categoryID,
                    $product->productName,
                    $product->productKeywords,
                    $product->productPrice,
                    $product->productDetail,
                    $product->productHref
                ];
                $colIndex = 1;
                foreach ($data as $value) {
                    $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
                    $sheet->setCellValue($cellCoordinate, $value);
                    $colIndex++;
                }
                $rowIndex++;
            }
            $fileIndex = str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            $newFileName = 'product_edit_' . $fileIndex . '.xlsx';
            $newFilePath = public_path('assets/excel/sellwing-products-editor/') . $newFileName;
            $writer = new Xlsx($spreadsheet);
            $writer->save($newFilePath);
        }
        return "success";
    }
}
