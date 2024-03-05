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
        $account = $this->processController->getSeller(self::USER_ID, self::VENDOR_ID);
        $products = $this->getVitsonMroProducts();
        $productsChunks = $products->chunk(100);
        $productCodes = [];
        foreach ($productsChunks as $index => $chunk) {
            if ($index > 51) {
                $tempFilePath = $this->genHrefsJsonFile($chunk, $index);
                $trackOverAmountProducts = $this->trackOverAmountProducts($tempFilePath);
                if ($trackOverAmountProducts === false) {
                    return false;
                }
                $productCodesJsonFile = storage_path('app/public/gdf/' . uniqid() . '.json');
                file_put_contents($productCodesJsonFile, json_encode($trackOverAmountProducts));
                $productCodes = array_merge($productCodes, $trackOverAmountProducts);
                unlink($tempFilePath);
            }
        }
        $b2bs = DB::table('vendors')
            ->where('is_active', 'Y')
            ->get();
        foreach ($productCodes as $productCode) {
            foreach ($b2bs as $b2b) {
                $b2bId = $b2b->id;
                $vendorEngName = $b2b->name_eng;
                $account = $this->processController->getAccount(self::USER_ID, $b2bId);
                $username = $account->username;
                $password = $account->password;
                $this->soldOutController->sendSoldOutRequest($productCode, $vendorEngName, $username, $password);
            }
        }
        return $this->gdfController->inactiveProducts($productCodes);
    }
    private function getVitsonMroProducts()
    {
        return DB::table('minewing_products')
            ->where('sellerID', self::VENDOR_ID)
            ->where('isActive', 'Y')
            ->orderBy('createdAt', 'asc')
            ->get(['productCode', 'productHref']);
    }
    private function genHrefsJsonFile($products, $index)
    {
        if ($products instanceof Collection) {
            $productsArray = $products->values()->toArray();
        } else {
            // 이미 배열인 경우, 직접 사용
            $productsArray = $products;
        }
        $tempFilePath = storage_path('app/public/gdf/sibal' . $index . '.json');
        file_put_contents($tempFilePath, json_encode($productsArray));
        return $tempFilePath;
    }
    private function trackOverAmountProducts($tempFilePath)
    {
        $scriptPath = public_path('js/gdf/vitsonmro.js');
        $command = "node {$scriptPath} {$tempFilePath}";
        exec($command, $output, $resultCode);
        if ($resultCode === 0 && isset($output[0])) {
            $productCodes = json_decode($output[0], true);
            return $productCodes;
        }
        return false;
    }
}
