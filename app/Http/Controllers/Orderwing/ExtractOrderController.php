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
        // 미네윙 제품에서 활성화된 제품 정보 검색
        $minewingQuery = DB::table('minewing_products')
            ->where('isActive', 'Y')
            ->where('productCode', $productCode)
            ->select('productHref', 'productImage');
        // 업로드된 제품과 수집된 제품을 조인하여 활성화된 제품 정보 검색
        $uploadedQuery = DB::table('uploaded_products AS up')
            ->join('collected_products AS cp', 'up.productId', '=', 'cp.id')
            ->where('up.isActive', 'Y')
            ->where('up.productId', $productCode)
            ->select('cp.productHref AS productHref', 'up.newImageHref AS productImage');
        // 두 쿼리 결과의 합집합에서 첫 번째 결과 반환
        $productHref = $minewingQuery->union($uploadedQuery)->first();
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
