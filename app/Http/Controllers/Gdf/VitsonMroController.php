<?php

namespace App\Http\Controllers\Gdf;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\ProcessController;
use App\Http\Controllers\Productwing\SoldOutController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VitsonMroController extends Controller
{
    private $processController;
    private $soldOutController;
    private $gdfController;
    const VENDOR_ID = 13;
    const USER_ID = 15;
    public function __construct()
    {
        $this->processController = new ProcessController();
        $this->soldOutController = new SoldOutController();
        $this->gdfController = new GdfController();
    }
    public function main()
    {
        set_time_limit(0);
        ini_set('memory_allow', '-1');
        $products = $this->getVitsonMroProducts();
        $genHrefsJsonFileResult = $this->genHrefsJsonFile($products);
        if (!$genHrefsJsonFileResult) {
            return $genHrefsJsonFileResult;
        }
        $productCodes = $genHrefsJsonFileResult;
        $jsonFilePath = storage_path("app/public/gdf/sold_out_target_product_codes.json");
        file_put_contents($jsonFilePath, json_encode($productCodes));
        return $jsonFilePath;
    }
    private function getVitsonMroProducts()
    {
        return DB::table('minewing_products')
            ->where('sellerID', self::VENDOR_ID)
            ->where('isActive', 'Y')
            ->where('createdAt', '<', '2024-03-07')
            ->orderBy('createdAt', 'asc')
            ->get(['productCode', 'productHref']);
    }
    private function genHrefsJsonFile($products)
    {
        $productChunks = $products->chunk(100);
        $productCodes = [];
        foreach ($productChunks as $index => $productChunk) {
            $productsArray = [];
            if ($productChunk instanceof Collection) {
                $productsArray = $productChunk->values()->toArray();
            } else {
                // 이미 배열인 경우, 직접 사용
                $productsArray = $productChunk;
            }
            $trackForbiddenProductsResult = null;
            $productCodesPath = null;
            $jsonFilePath = storage_path("app/public/gdf/product_chunk_$index.json");
            file_put_contents($jsonFilePath, json_encode($productsArray));
            $trackForbiddenProductsResult = $this->trackForbiddenProducts($jsonFilePath);

            // 변경된 부분: 실패한 경우 건너뛰고 계속 진행합니다.
            if ($trackForbiddenProductsResult !== false) {
                // 성공한 경우만 처리
                $productCodesPath = storage_path("app/public/gdf/product_codes_$index.json");
                file_put_contents($productCodesPath, json_encode($trackForbiddenProductsResult));
                $productCodes = array_merge($productCodes, $trackForbiddenProductsResult);
            } else {
                echo $index;
            }
        }
        return $productCodes;
    }
    private function trackForbiddenProducts($jsonFilePath)
    {
        $scriptPath = public_path('js/gdf/vitsonmro.js');
        $command = "node {$scriptPath} {$jsonFilePath}";
        exec($command, $output, $resultCode);
        if ($resultCode === 0 && isset($output[0])) {
            $productCodes = json_decode($output[0], true);
            return $productCodes;
        }
        return false;
    }
}
