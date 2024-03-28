<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvalidProductsProtocol extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:invalid-products-protocol {vendorId}';

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
        $vendorId = $this->argument('vendorId');
        $vendor = $this->getVendor($vendorId);
        $account = $this->getAccount($vendorId);
        $products = $this->getProducts($vendorId);
        $mappedProducts = array_map(function ($product) {
            return [
                'productCode' => $product->productCode,
                'productHref' => $product->productHref
            ];
        }, $products);
        $productsChunks = array_chunk($mappedProducts, 100);
        foreach ($productsChunks as $index => $productsChunk) {
            $productsChunkFilePath = $this->createProductsChunkFile($productsChunk);
            $trackProductsResult = $this->trackProducts($productsChunkFilePath, $vendor->name_eng);
            if ($trackProductsResult === false) {
                echo $productsChunkFilePath . ' / ' . $index;
                return $productsChunkFilePath;
            } else {
                if (count($trackProductsResult) > 0) {
                    $this->createResultFile($trackProductsResult);
                }
            }
        }
        echo "success";
        return;
    }
    private function createResultFile($invalidProductCodes)
    {
        $directoryPath = storage_path('app/public/invalid-products-protocol/results/');
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0777, true);
        }
        $filePath = $directoryPath . Str::uuid() . '.json';
        file_put_contents($filePath, json_encode($invalidProductCodes));
    }
    private function trackProducts($productsChunkFilePath, $vendorEngName)
    {
        $directoryPath = public_path('js/gdf/');
        $scriptPath = $directoryPath . $vendorEngName . '.js';
        $command = "node {$scriptPath} {$productsChunkFilePath}";
        exec($command, $output, $resultCode);
        if ($resultCode === 0 && isset($output[0])) {
            $productCodes = json_decode($output[0]);
            return $productCodes;
        }
        return false;
    }
    private function createProductsChunkFile($productsChunk)
    {
        $directoryPath = storage_path('app/public/invalid-products-protocol/targets/');
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0777, true);
        }
        $productsChunkFilePath = $directoryPath . Str::uuid() . '.json';
        file_put_contents($productsChunkFilePath, json_encode($productsChunk));
        return $productsChunkFilePath;
    }
    private function getVendor($vendorId)
    {
        return DB::table('product_search AS ps')
            ->join('vendors AS v', 'v.id', '=', 'ps.vendor_id')
            ->where('v.id', $vendorId)
            ->first();
    }
    private function getProducts($vendorId)
    {
        return DB::table('minewing_products')
            ->where('isActive', 'Y')
            ->where('sellerID', $vendorId)
            ->where('createdAt', '<', '2024-03-27 14:00:00')
            ->get(['productCode', 'productHref'])
            ->toArray();
    }
    private function getAccount($vendorId)
    {
        return DB::table('accounts')
            ->where('vendor_id', $vendorId)
            ->first();
    }
}
