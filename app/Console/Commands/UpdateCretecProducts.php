<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpParser\Node\Expr\Cast\Object_;

class UpdateCretecProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-cretec-products';

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
        $scrapeResult = $this->scrapeProducts();
        if ($scrapeResult['status'] === false) {
            print_r($scrapeResult);
        }
        $fetchedProducts = $scrapeResult['data'];
        print_r($fetchedProducts);
        echo count($fetchedProducts);
    }
    protected function scrapeProducts()
    {
        $products = [];
        for ($i = 0; $i < 3; $i++) {
            $tempFilePath = storage_path('app/public/urls/cretec_products_' . $i . '.json');
            $scriptPath = public_path('js/minewing/details/cretec.js');
            $command = "node {$scriptPath} {$tempFilePath}";
            exec($command, $output, $returnCode);
            if ($returnCode === 0 && isset($output[0])) {
                $tmpProducts = json_decode($output[0], true);
                $products = array_merge($products, $tmpProducts);
            } else {
                return [
                    'status' => false,
                    'message' => $i . '번째 상품군에서 에러가 발생했습니다.',
                    'error' => ''
                ];
            }
        }
        return [
            'status' => true,
            'message' => 'success',
            'data' => $products
        ];
    }
    protected function getCretecProducts()
    {
        return DB::table('minewing_products')
            ->where('sellerID', 61)
            ->where('isActive', 'Y')
            ->limit(101)
            ->select(['id', 'productHref'])
            ->get();
    }
    protected function extractProductUrls(Object $products)
    {
        $productUrls = [];
        foreach ($products as $product) {
            $parsedUrl = parse_url($product->productHref);
            $queryStr = $parsedUrl['query'];
            parse_str($queryStr, $queryParams);
            $itemCd = $queryParams['itemCd'];
            $productUrl = "https://jsktec.toolpark.kr/product/product-detail.do?goods_code=" . $itemCd;
            $productUrls[] = [
                'id' => $product->id,
                'productUrl' => $productUrl
            ];
        }
        return $productUrls;
    }
}
