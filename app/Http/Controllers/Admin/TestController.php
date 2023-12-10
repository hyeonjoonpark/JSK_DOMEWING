<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\NameController;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use DB;
use Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TestController extends Controller
{
    public function index()
    {
        for ($i = 1; $i <= 8; $i++) {
            $oldProducts = $this->extractOldData($i);
            $productIDs = array_column($oldProducts, 'productID');
            $dBProducts = $this->getdBProducts($productIDs);
            $this->postExcelForm($i, $oldProducts);
            $this->genInExcel($i, $dBProducts);
        }
        return 'success';
    }
    public function postExcelForm($index, $oldProducts)
    {
        // Create a new spreadsheet and set the active sheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Define the headers
        $headers = ['A1' => 'Product ID', 'B1' => 'Product Name', 'C1' => 'Product Price'];
        // Set headers in the spreadsheet
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        // Populate the data
        $row = 2;
        foreach ($oldProducts as $product) {
            $productId = $product['productID'];
            $productName = $this->preprocessProductName($product['productName']);
            $productPrice = $product['productPrice'];

            $sheet->setCellValue("A{$row}", $productId)
                ->setCellValue("B{$row}", $productName)
                ->setCellValue("C{$row}", $productPrice);
            $row++;
        }

        // Save the spreadsheet to a file
        $writer = new Xlsx($spreadsheet);
        $fileName = $index . '.xlsx';
        $path = public_path('assets/excel/uploaded/' . $fileName);
        $writer->save($path);

        return $path; // Return the file path for further use
    }
    public function preprocessProductName($productName)
    {
        $nameController = new NameController();
        $productName = $nameController->index($productName);
        return $productName;
    }
    public function genInExcel($index, $dBProducts)
    {
        // Create a new spreadsheet and set the active sheet
        $spreadsheet = IOFactory::load(public_path('assets/excel/uploaded/' . $index . '.xlsx'));
        $sheet = $spreadsheet->getActiveSheet();

        // Define the headers
        $headers = ['A1' => 'Product ID', 'B1' => 'Product Name', 'C1' => 'Product Price'];

        // Set headers in the spreadsheet
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Populate the data
        $row = 2;
        foreach ($dBProducts as $product) {
            if (isset($product->productId)) {
                $productId = (int) $product->productId;
                $productName = $this->preprocessProductName($product->newProductName);
                $productPrice = (int) ceil($product->productPrice * 1.15);

                $sheet->setCellValue("A{$row}", $productId)
                    ->setCellValue("B{$row}", $productName)
                    ->setCellValue("C{$row}", $productPrice);
            }
            $row++;
        }

        // Save the spreadsheet to a file
        $writer = new Xlsx($spreadsheet);
        $fileName = $index . '.xlsx';
        $path = public_path('assets/excel/uploaded/' . $fileName);
        $writer->save($path);

        return $path; // Return the file path for further use
    }

    public function getdBProducts($productIDs)
    {
        $dBProducts = DB::table('uploaded_products AS up')
            ->join('collected_products AS cp', 'up.productId', '=', 'cp.id')
            ->whereIn('up.productId', $productIDs)
            ->get();
        return $dBProducts;
    }
    public function extractOldData($index)
    {
        $filePath = public_path('assets/excel/uploaded/ownerclan (' . $index . ').xlsx');
        try {
            $spreadsheet = IOFactory::load($filePath);
        } catch (Exception $e) {
            // Handle the exception or return an error message
            return ['error' => 'Unable to load spreadsheet: ' . $e->getMessage()];
        }

        $sheet = $spreadsheet->getActiveSheet();
        $oldProducts = [];

        foreach ($sheet->getRowIterator(4) as $row) {
            $productIDCell = $row->getCellIterator()->seek('S')->current(); // 12th column, 'L'
            $productNameCell = $row->getCellIterator()->seek('F')->current(); // 6th column, 'F'
            $productPriceCell = $row->getCellIterator()->seek('L')->current(); // 12th column, 'L' again

            $productIDParts = explode(',', $productIDCell->getValue());
            if (count($productIDParts) >= 3) {
                $productID = $productIDParts[2];
            } else {
                $productID = '-1';
            }

            $oldProduct = [
                'productID' => (int) $productID,
                'productName' => (string) $productNameCell->getValue(),
                'productPrice' => (int) $productPriceCell->getValue(),
            ];

            $oldProducts[] = $oldProduct;
        }

        return $oldProducts;
    }
}
