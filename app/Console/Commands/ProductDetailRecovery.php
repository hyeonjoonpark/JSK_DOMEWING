<?php

namespace App\Console\Commands;

use DOMDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductDetailRecovery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:product-detail-recovery';

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
        $products = $this->getTargetProducts();
        print_r($this->processProducts($products));
    }
    protected function scrapeProductDetail($filePath)
    {
        $scriptPath = public_path('js/detail-recovery/ds1008.js');
        $account = DB::table('accounts')
            ->where('vendor_id', 14)
            ->first(['username', 'password']);
        $username = $account->username;
        $password = $account->password;
        $command = "node {$scriptPath} {$filePath} {$username} $password";
        exec($command, $output, $resultCode);
        if ($resultCode === 0 && isset($output[0])) {
            $products = json_decode($output[0], true);
        }
    }
    protected function processProducts($products)
    {
        $chunkedProducts = array_chunk($products, 100, false);
        foreach ($chunkedProducts as $products) {
            $tempProductCodeFilePath = public_path('js/detail-recovery/ds1008_targets.json');
            file_put_contents($tempProductCodeFilePath, json_encode($products));
            $this->scrapeProductDetail($tempProductCodeFilePath);
            unlink($tempProductCodeFilePath);
        }
    }
    protected function getTargetProducts()
    {
        $productCodeFile = file_get_contents(storage_path('app/public/product-codes/product_detail_recovery.json'));
        $productCodes = json_decode($productCodeFile);
        $products = DB::table('minewing_products')
            ->whereIn('productCode', $productCodes)
            ->where('sellerID', 14) // 씨오코리아
            ->get(['productDetail', 'productHref', 'id'])
            ->toArray();
        return $products;
    }
}
