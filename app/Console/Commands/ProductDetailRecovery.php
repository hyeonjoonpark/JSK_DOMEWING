<?php

namespace App\Console\Commands;

use DOMDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        $productCodeFilePath = storage_path('app/public/product-codes/product_detail_recovery.json');
        $productCodeFile = file_get_contents($productCodeFilePath);
        $productCodes = json_decode($productCodeFile);
        $products = DB::table('minewing_products')
            ->whereIn('productCode', $productCodes)
            ->where('productDetail', 'like', '%옵션%')
            ->get(['productCode']);
        foreach ($products as $product) {
            echo $product->productCode . "\n";
        }
    }
    protected function processProductOption(string $originProductDetail): string
    {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($originProductDetail);
        libxml_clear_errors();
        $optionName = "";
        $h1Tags = $doc->getElementsByTagName('h1');
        if ($h1Tags->length > 0) {
            $optionName = $h1Tags->item(0)->textContent;
        }
        return $optionName;
    }
    protected function processProductDetail(array $productDetailImages, string $optionName): string
    {
        if (strlen($optionName) > 0) {
            $html = '
            <h1 style="color:red !important; font-weight:bold !important; font-size:4rem !important;">' . $optionName . '</h1><br><br><br>';
        } else {
            $html = '';
        }
        $html .= '
        <center>
            <img src="https://www.sellwing.kr/images/CDN/ladam_header.jpg"><br>';
        foreach ($productDetailImages as $productDetailImage) {
            $html .= '
            <img src="' . $productDetailImage . '"><br>
            ';
        }
        $html .= '
        </center>
        ';
        return $html;
    }
}
