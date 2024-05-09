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
            ->where('sellerID', 50)
            ->get(['productCode', 'productHref', 'productDetail']);
        echo (count($products));
        $hrefs = [];
        $productCodes = [];
        $productDetails = [];
        foreach ($products as $product) {
            $hrefs[] = $product->productHref;
            $productCodes[] = $product->productCode;
            $productDetails[] = $product->productDetail;
        }

        // JSON 데이터를 임시 파일에 저장
        $tempFilePath = tempnam(sys_get_temp_dir(), 'hrefs_');
        file_put_contents($tempFilePath, json_encode($hrefs));
        // Node.js 스크립트 실행, 파일 경로를 인자로 전달
        // $command = "node public/js/detail-recovery/vitsonmro.js $tempFilePath jskorea2023 Tjddlf88!@#"; //비츠온엠알오 13
        // $command = "node public/js/detail-recovery/ds1008.js $tempFilePath sungil2018 tjddlf88!@#"; //씨오코리아 14
        $command = "node public/js/detail-recovery/cheonyu.js $tempFilePath jskorea2023 Tjddlf88!@"; //천유닷컴 50
        exec($command, $output, $return_var);

        // 결과 출력
        foreach ($output as $productInfoJson) {
            $productInfos = json_decode($productInfoJson, true);  // JSON 문자열을 배열로 변환
            foreach ($productInfos as $productInfo) {  // 모든 제품 정보를 반복 처리
                if (isset($productInfo['productDetail']) && is_array($productInfo['productDetail'])) {
                    $productDetailUrls = $productInfo['productDetail'];  // 상세 이미지 URL 배열
                    $optionName = $this->processProductOption($productDetailUrls[0]);  // 옵션 이름 추출, 첫 번째 이미지 URL을 사용
                    $finalHtml = $this->processProductDetail($productDetailUrls, $optionName);  // 최종 HTML 생성
                    echo $finalHtml;  // 결과 출력
                } else {
                    echo "Product detail key missing or not an array for product: " . $productInfo['productName'] . "\n";
                }
            }
        }
        // 임시 파일 삭제
        unlink($tempFilePath);
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
