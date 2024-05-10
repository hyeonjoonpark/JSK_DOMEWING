<?php

namespace App\Http\Controllers\Orderwing;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExtractOrderController extends Controller
{
    public function index($b2Bs)
    {
        $data = [];
        foreach ($b2Bs as $b2B) {
            $b2BEngName = $b2B->name_eng;
            $excelPath = $this->getExcelPath($b2BEngName);
            if ($excelPath !== null) {
                $tmpData = $this->extractExcelData($excelPath, $b2BEngName);
                $data = array_merge($data, $tmpData);
            }
        }
        return $data;
    }

    private function processDomesin($b2BEngName)
    {
        $excelPath = public_path("assets/excel/orderwing/{$b2BEngName}/");
        $data = [];

        // Check if the directory exists, if not, create it
        if (!is_dir($excelPath)) {
            mkdir($excelPath, 0755, true);
        }

        $files = scandir($excelPath, SCANDIR_SORT_DESCENDING);

        foreach ($files as $file) {
            $fullExcelPath = $excelPath . $file;

            if (is_file($fullExcelPath)) {
                $tmpData = $this->extractExcelData($fullExcelPath, $b2BEngName);
                $data = array_merge($data, $tmpData);
            }
        }

        return $data;
    }

    public function getProductHref($productCode)
    {
        $productHref = DB::table('minewing_products')
            ->where('isActive', 'Y')
            ->where('productCode', $productCode)
            ->select('productHref', 'productImage')
            ->first();

        if ($productHref === null) {
            $productHref = $this->getUploadedProductHref($productCode);
        }

        if ($productHref === null) {
            return [
                'status' => false,
                'return' => '상품 정보를 찾을 수 없습니다.',
            ];
        }

        return [
            'status' => true,
            'return' => $productHref,
        ];
    }

    private function getUploadedProductHref($productCode)
    {
        return DB::table('uploaded_products AS up')
            ->join('collected_products AS cp', 'up.productId', '=', 'cp.id')
            ->where('up.isActive', 'Y')
            ->where('up.productId', $productCode)
            ->select('cp.productHref AS productHref', 'up.newImageHref AS productImage')
            ->first();
    }

    public function extractExcelData($excelPath, $b2BEngName)
    {
        if (File::exists($excelPath)) {
            $processDataController = new ProcessDataController();
            try {
                return $processDataController->$b2BEngName($excelPath);
            } catch (\Exception $e) {
                return [
                    'status' => false,
                    'message' => '엑셀 파일로부터 데이터를 추출하는 과정에서 오류가 발생했습니다.<br>오류 업체명: ' . $b2BEngName,
                    'error' => $e->getMessage()
                ];
            }
        }
        return [];
    }

    private function getExcelPath($b2BEngName)
    {
        $excelPath = public_path("assets/excel/orderwing/{$b2BEngName}/");

        // Check if the directory exists, if not, create it
        if (!is_dir($excelPath)) {
            mkdir($excelPath, 0755, true);
        }

        $files = scandir($excelPath, SCANDIR_SORT_DESCENDING);

        $newestFile = null;
        $newestFileTime = 0;

        foreach ($files as $file) {
            $fullPath = $excelPath . $file;

            if (is_file($fullPath)) {
                $fileModTime = filemtime($fullPath);

                if ($fileModTime > $newestFileTime) {
                    $newestFile = $file;
                    $newestFileTime = $fileModTime;
                }
            }
        }

        return $newestFile === null ? null : $excelPath . $newestFile;
    }
}
