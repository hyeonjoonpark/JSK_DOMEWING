<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Admin\FormProductController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class ExcelwingController
{
    public function downloadExcel(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        $b2BID = $request->b2BID;
        $sellerID = $request->sellerID;
        $partnerMargin = $request->marginRate;
        $shippingFee = $this->getShippingFee($sellerID);
        $products = $this->getProducts($sellerID);
        $numChunks = $this->getNumChunks($b2BID);
        $productsChunks = array_chunk($products->toArray(), $numChunks);
        $marginRate = $this->getMarginRate($b2BID, $partnerMargin);
        $vendor = $this->getVendor($b2BID);
        $vendorEngName = $vendor->name_eng;
        $formProductController = new FormProductController();
        $downloadURLs = [];
        // Process each chunk
        foreach ($productsChunks as $index => $productsChunk) {
            $response = $formProductController->$vendorEngName($productsChunk, $marginRate, $vendorEngName, $shippingFee, $index);
            if ($response['status']) {
                $downloadURLs[] = $response['return'];
            } else {
                return [
                    'status' => false,
                    'return' => '엑셀 파일에 데이터를 기록하던 중 오류가 발생했습니다.'
                ];
            }
        }
        // Create ZIP file
        return $this->createZipFile($vendorEngName, $downloadURLs);
    }
    private function getProducts($sellerID)
    {
        $products = DB::table("minewing_products")
            ->where("isActive", "Y")
            ->where("sellerID", $sellerID)
            ->whereNotNull('categoryID')
            ->get();

        return $products;
    }

    private function getMarginRate($vendorID, $partnerMargin)
    {
        $marginRate = DB::table("product_register")
            ->where("vendor_id", $vendorID)
            ->value('excel_margin_rate');
        $marginRate = (float)((100 + (int)$marginRate) / 100);
        $partnerMargin = (float)((100 + (int)$partnerMargin) / 100);
        $sellwingRate = 1.1;
        return $marginRate * $partnerMargin * $sellwingRate;
    }

    private function getVendor($vendorID)
    {
        $vendor = DB::table("product_register AS pr")
            ->join("vendors AS v", 'pr.vendor_id', '=', 'v.id')
            ->where("v.is_active", "ACTIVE")
            ->where("pr.is_active", "Y")
            ->where('v.id', $vendorID)
            ->select("v.id", "v.name", "v.name_eng")
            ->first();
        return $vendor;
    }

    private function getShippingFee($vendorID)
    {
        $shippingFee = DB::table('product_search')
            ->where('vendor_id', $vendorID)
            ->select('shipping_fee')
            ->first()
            ->shipping_fee;
        return $shippingFee;
    }

    private function getNumChunks($b2BID)
    {
        $numChunksMapping = [
            35 => 300,
            33 => 100,
        ];

        return $numChunksMapping[$b2BID] ?? 500;
    }

    private function createZipFile($vendorEngName, $downloadURLs)
    {
        $zip = new ZipArchive();
        $zipFileName = public_path('assets/excel/formed/' . $vendorEngName . '_' . date('YmdHis') . '.zip');

        if ($zip->open($zipFileName, ZipArchive::CREATE) === TRUE) {
            foreach ($downloadURLs as $file) {
                $localPath = public_path(parse_url($file, PHP_URL_PATH));
                if (file_exists($localPath)) {
                    $zip->addFile($localPath, basename($localPath));
                } else {
                    $zip->close();
                    return [
                        'status' => false,
                        'return' => '파일을 찾을 수 없습니다: ' . $localPath
                    ];
                }
            }
            $zip->close();
            $urlZip = asset('assets/excel/formed/' . basename($zipFileName));
            return [
                'status' => true,
                'return' => $downloadURLs,
                'urlZip' => $urlZip
            ];
        } else {
            return [
                'status' => false,
                'return' => '압축 파일 생성에 실패했습니다.'
            ];
        }
    }
}
