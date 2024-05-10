<?php

namespace App\Console\Commands;

use DOMDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            // ->where('productDetail', 'like', '%옵션%')
            ->where('sellerID', 14)
            ->limit(3)
            ->get(['productCode', 'productHref', 'productDetail']);
        echo (count($products));
        // return;
        $hrefs = [];
        foreach ($products as $product) {
            $hrefs[] = [
                'href' => $product->productHref,
                'code' => $product->productCode  // 제품 코드도 저장
            ];
        }

        // JSON 데이터를 임시 파일에 저장
        $tempFilePath = tempnam(sys_get_temp_dir(), 'hrefs_');
        file_put_contents($tempFilePath, json_encode($hrefs));
        // Node.js 스크립트 실행, 파일 경로를 인자로 전달
        // $command = "node public/js/detail-recovery/vitsonmro.js $tempFilePath jskorea2023 Tjddlf88!@#"; //비츠온엠알오 13
        $command = "node public/js/detail-recovery/ds1008.js $tempFilePath sungil2018 tjddlf88!@#"; //씨오코리아 14
        // $command = "node public/js/detail-recovery/cheonyu.js $tempFilePath jskorea2023 Tjddlf88!@"; //천유닷컴 50
        exec($command, $output, $return_var);

        // 결과 출력
        foreach ($output as $productInfoJson) {
            $productInfos = json_decode($productInfoJson, true);  // JSON 문자열을 배열로 변환
            foreach ($productInfos as $productInfo) {  // 모든 제품 정보를 반복 처리
                if (isset($productInfo['productDetail']) && is_array($productInfo['productDetail'])) {
                    $originDetailOption = DB::table('minewing_products')
                        ->where('productCode', $productInfo['productCode'])
                        ->get('productDetail');

                    $productDetailUrls = $productInfo['productDetail'];  // 상세 이미지 URL 배열
                    $optionName = $this->processProductOption($originDetailOption);  // 옵션 이름 추출, 첫 번째 이미지 URL을 사용
                    $realUrl = $this->saveProductImages($productDetailUrls, $productInfo['productCode']); //이미지 저장
                    $finalHtml = $this->processProductDetail($realUrl, $optionName);  // 최종 HTML 생성


                    echo $finalHtml;
                    DB::table('minewing_products')
                        ->where('productCode', $productInfo['productCode'])
                        ->update(['productDetail' => $finalHtml]);
                }
            }
        }
        // 임시 파일 삭제
        unlink($tempFilePath);
    }
    protected function saveProductImages(array $productDetailImages, $productCode)
    {
        $baseDir = "public/images/CDN/detail";
        if (!file_exists($baseDir)) {
            mkdir($baseDir, 0777, true);
        }
        $localImagePaths = [];
        foreach ($productDetailImages as $index => $imageUrl) {
            $encodedUrl = $this->encodeImageUrl($imageUrl);
            $imageContent = file_get_contents($encodedUrl);
            $filename = basename($imageUrl);  // URL에서 파일명 추출
            $filePath = "$baseDir/$filename";  // 저장할 파일 경로
            file_put_contents($filePath, $imageContent);
            echo "Image saved to: $filePath\n";
            $localImagePaths[] = $filePath;  // 로컬 저장 경로 추가
        }
        return $localImagePaths;
    }
    protected function encodeImageUrl($url)
    {
        // URL의 구성 요소를 분해
        $parts = parse_url($url);

        // 경로 부분에 있는 특수 문자 인코딩
        if (isset($parts['path'])) {
            $parts['path'] = implode("/", array_map("rawurlencode", explode("/", $parts['path'])));
        }

        // 쿼리 스트링 처리
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
            $parts['query'] = http_build_query($query);
        }

        // URL 재구성
        return $parts['scheme'] . '://' . $parts['host'] . (isset($parts['path']) ? $parts['path'] : '') .
            (isset($parts['query']) ? '?' . $parts['query'] : '');
    }
    protected function processProductDetail(array $productImagePaths, string $optionName): string
    {
        $html = $optionName ? "<h1 style='color:red; font-weight:bold; font-size:4rem;'>$optionName</h1><br><br><br>" : '';
        $html .= "<center>";
        foreach ($productImagePaths as $imagePath) {
            $html .= "<img src='$imagePath'><br>";  // 저장된 이미지의 로컬 경로를 src에 사용
        }
        $html .= "</center>";
        return $html;
    }

    function decodeUnicodeEscapeSequence($str)
    {
        // 유니코드 이스케이프 시퀀스를 UTF-8 문자열로 변환
        return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
        }, $str);
    }

    protected function processProductOption($originProductDetail)
    {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML(mb_convert_encoding($originProductDetail, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();
        $optionName = "";
        $h1Tags = $doc->getElementsByTagName('h1');
        if ($h1Tags->length > 0) {
            $optionName = $h1Tags->item(0)->textContent;
            // 유니코드 이스케이프 시퀀스 디코딩
            $optionName = $this->decodeUnicodeEscapeSequence($optionName);
        }
        return $optionName;
    }
}
