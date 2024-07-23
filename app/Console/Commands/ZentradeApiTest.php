<?php

namespace App\Console\Commands;

use App\Http\Controllers\Product\NameController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class ZentradeApiTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:zentrade-api-test';

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
        $response = Http::get('https://www.zentrade.co.kr/shop/proc/product_api.php', [
            'id' => 'sungiltradekorea',
            'm_skey' => '136b86dfcdf3d08f4f1b4270c1b3fddd',
            'runout' => 0
        ]);
        $xml = simplexml_load_string($response->body());
        $i = 0;
        foreach ($xml->product as $product) {
            if ($i > 10) {
                break;
            }
            print_r($product);
            // $processProductResult = $this->processProduct($product);
            $i++;
        }
    }
    /**
     * 반복문 내의 상품을 가공하는 메소드입니다.
     * @param SimpleXMLElement $product
     * @return array
     */
    protected function processProduct(SimpleXMLElement $product)
    {
        $nc = new NameController();
        $hasOption = $this->hasProductOption($product);
        return [
            'sellerID' => 92,
            'userID' => 15,
            'categoryID' => null,
            'partner_id' => null,
            'origin_product_code' => (string)trim($product['code']),
            'productCode' => $this->generateProductCode(8),
            'productKeywords' => trim((string) $product->keyword),
            'productPrice' => (int)ceil((float)$product->price['buyprice']),
            'shipping_fee' => 3000,
            'productImage' => $this->getProductImage($product->listimg->attributes()),
            'productHref' => 'https://www.zentrade.co.kr/shop/goods/goods_view.php?goodsno=' . (string)trim($product['code']),
            'hasOption' => $hasOption,
            'productName' => $hasOption === 'Y' ? $this->processProductName((string) $product->prdtname, (string)$product->option) : $nc->index((string) $product->prdtname),
            'productDetail' => trim((string) $product->content),
        ];
    }
    /**
     * 셀윙 상품 고유 코드를 생성하는 메소드입니다.
     * @param int $length 상품 코드 문자열 길이
     * @return string 상품 코드
     */
    protected function generateProductCode(int $length): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charactersLength = strlen($characters);
        do {
            $randomCode = '';
            for ($i = 0; $i < $length; $i++) {
                $randomCode .= $characters[rand(0, $charactersLength - 1)];
            }

            // Check if the generated code already exists
            $isExist = DB::table('minewing_products')
                ->where('productCode', $randomCode)
                ->where('isActive', 'Y')
                ->exists();
        } while ($isExist);
        return $randomCode;
    }
    /**
     * 젠트레이드 상품의 대표 이미지들 중 마지막 인덱스 이미지를 추출합니다.
     * @param SimpleXMLElement $attributes
     * @return string
     */
    protected function getProductImage(SimpleXMLElement $attributes)
    {
        $listimgs = [];
        foreach ($attributes as $key => $value) {
            $listimgs[] = (string) $value;
        }
        $lastImage = end($listimgs);
        return $lastImage;
    }
    /**
     * 상품에 옵션이 있는지 여부를 판단합니다.
     * @param SimpleXMLElement $product
     * @return string 'Y' 또는 'N'
     */
    protected function hasProductOption(SimpleXMLElement $product): string
    {
        $options = (string) $product->option;
        return trim($options) !== '' ? 'Y' : 'N';
    }
    protected function processProductName(string $productName, string $productOptions)
    {
    }
}
