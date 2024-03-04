<?php

namespace App\Http\Controllers\Gdf;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\ProcessController;
use Illuminate\Support\Facades\DB;

class VitsonMroController extends Controller
{
    private $processController;
    const VENDOR_ID = 13;
    const USER_ID = 15;
    public function __construct()
    {
        $this->processController = new ProcessController();
    }
    public function main()
    {
        $account = $this->processController->getSeller(self::USER_ID, self::VENDOR_ID);
        $products = $this->getVitsonMroProducts();
        $tempFilePath = $this->genHrefsJsonFile($products);
        $trackOverAmountProducts = $this->trackOverAmountProducts($tempFilePath);
        if ($trackOverAmountProducts === false) {
            return false;
        }
        return $trackOverAmountProducts;
    }
    private function getVitsonMroProducts()
    {
        return DB::table('minewing_products')
            ->where('sellerID', self::VENDOR_ID)
            ->get(['productCode', 'productHref']);
    }
    private function genHrefsJsonFile($products)
    {
        $tempFilePath = storage_path('app/public/gdf/' . uniqid() . '.json');
        file_put_contents($tempFilePath, json_encode($products));
        return $tempFilePath;
    }
    private function trackOverAmountProducts($tempFilePath)
    {
        $scriptPath = public_path('js/gdf/vitsonmro.js');
        $command = "node {$scriptPath} {$tempFilePath}";
        exec($command, $output, $resultCode);
        if ($resultCode === 0 && isset($output[0])) {
            $productCodes = $output[0];
            return $productCodes;
        }
        return false;
    }
}
