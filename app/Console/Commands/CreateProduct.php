<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\ProductDataValidityController;
use App\Http\Controllers\Minewing\SaveController;
use App\Http\Controllers\Product\NameController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-product';

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
        $nc = new NameController();
        $pdvc = new ProductDataValidityController();
        $sc = new SaveController();
        //string $productDetail, string $productHref, string $hasOption, string $remark = ''
        $sellerId = 68;
        $categoryId = 2264;
        $productName = 'TEA1ONE 티원원 새로운에너지충전 블랜디드 티세트(컬렉션4종)';
        $productName = $nc->index($productName);
        $productKeywords = '국산차,전통차,한국하동차,건강차,피로회복,블렌디드티,선물용차,티원원';
        $validateKeywords = $pdvc->validateKeywords($productKeywords);
        if (!$validateKeywords) {
            $this->info($validateKeywords);
            return;
        }
        $productPrice = 16500;
        $shippingFee = 3000;
        $bundleQuantity = 4;
        $productImage = 'https://www.sellwing.kr/images/CDN/product/140732369.jpg';
        $productDetail = '
        <center>
            <img src="https://www.sellwing.kr/images/CDN/tahoeshop_header.jpg" alt="타호샵 헤더 이미지"><br>
            <img src="https://www.sellwing.kr/images/CDN/detail/140732369.gif" alt=""><br>
            <img src="https://www.sellwing.kr/images/CDN/detail/14073236903.jpg" alt=""><br>
        </center>
        ';
        $productHref = 'https://smartstore.naver.com/tahoeshop/products/10227157515';
        $hasOption = 'N';
        DB::table('minewing_products')
            ->insert([
                'userID' => 15,
                'productCode' => $sc->generateRandomProductCode(8),
                'sellerID' => $sellerId,
                'categoryID' => $categoryId,
                'productName' => $productName,
                'productKeywords' => $productKeywords,
                'productPrice' => $productPrice,
                'shipping_fee' => $shippingFee,
                'bundle_quantity' => $bundleQuantity,
                'productImage' => $productImage,
                'productDetail' => $productDetail,
                'productHref' => $productHref,
                'hasOption' => $hasOption
            ]);
        $this->info("The product has been successfully created!");
    }
}
