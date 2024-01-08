<?php

namespace App\Http\Controllers\Orderwing;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ExtractOrderController extends Controller
{
    public function index($b2Bs)
    {
        $data = [];
        foreach ($b2Bs as $index => $b2B) {
            $b2BEngName = $b2B->name_eng;
            $excelPath = $this->getExcelPath($b2BEngName);
            if ($excelPath == -1) {
                continue;
            }
            $tmpData = $this->extractExcelData($excelPath, $b2BEngName);
            $data = array_merge($data, $tmpData);
        }
        return $data;
    }
    public function getProductHref($productCode)
    {
        // First query
        $firstQuery = DB::table('minewing_products')
            ->where('isActive', 'Y')
            ->where('productCode', $productCode)
            ->select('productHref', 'productImage');

        // Second query with union
        $productHref = DB::table('uploaded_products AS up')
            ->join('collected_products AS cp', 'up.productId', '=', 'cp.id')
            ->where('up.isActive', 'Y')
            ->where('up.productId', $productCode)
            ->select('cp.productHref', 'up.newImageHref AS productImage') // Ensure this select statement is correctly referencing the column from 'collected_products'
            ->union($firstQuery) // Union with the first query
            ->first(); // Get the first result of the union
        return $productHref;
    }
    public function extractExcelData($excelPath, $b2BEngName)
    {
        $processDataController = new ProcessDataController();
        return $processDataController->$b2BEngName($excelPath);
    }
    public function getExcelPath($b2BEngName)
    {
        $excelPath = public_path('assets/excel/orderwing/' . $b2BEngName . '/');

        // Get all files in the directory
        $files = scandir($excelPath, SCANDIR_SORT_DESCENDING);

        // Initialize variables to store the newest file data
        $newestFile = '';
        $newestFileTime = 0;

        // Iterate over each file in the directory
        foreach ($files as $file) {
            // Construct the full path for each file
            $fullPath = $excelPath . $file;

            // Ensure it's a file and not a directory
            if (is_file($fullPath)) {
                // Get the file modification time
                $fileModTime = filemtime($fullPath);

                // Check if this file is the newest so far
                if ($fileModTime > $newestFileTime) {
                    $newestFile = $file;
                    $newestFileTime = $fileModTime;
                }
            }
        }
        if ($newestFile == '') {
            return -1;
        }
        return $excelPath . $newestFile;
    }
}
