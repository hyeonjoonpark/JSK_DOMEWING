<?php

namespace App\Http\Controllers\Recover;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Exception;

class IndexController extends Controller
{
    public function index()
    {
        set_time_limit(0);
        $products = $this->getProducts(3);
        $errors = [];
        foreach ($products as $product) {
            $response = $this->getFileNames($product);
            if ($response['status'] === false) {
                $productImageFileName = $response['return']['productImage'];
                $productDetailFileNames = $response['return']['productDetailImages'];
                $productHref = $product->productHref;
                $scrapeProductImageFiles = $this->scrapeProductImageFiles($productHref);
                if ($scrapeProductImageFiles['status'] === false) {
                    $errors[] = $productHref;
                    continue;
                }
                $productContents = $scrapeProductImageFiles['return'];
                $productImageFile = $productContents['productImage'];
                $productDetailFiles = $productContents['productDetail'];
                $this->sibal($productImageFile, $productImageFileName, 'product');
                for ($i = 0; $i < count($productDetailFileNames); $i++) {
                    $this->sibal($productDetailFiles[$i], $productDetailFileNames[$i], 'detail');
                }
            }
        }
        return $errors;
    }
    public function scrapeProductImageFiles($productHref)
    {
        $scriptPath = public_path('js/recovery/dometopia.js');
        $command = "node " . escapeshellarg($scriptPath) . " " . escapeshellarg($productHref);
        try {
            exec($command, $output, $returnCode);
            // 실행 결과 확인
            if ($returnCode !== 0 || !isset($output[0])) {
                return [
                    'status' => false,
                    'return' => '상품 정보 추출 과정에서 오류가 발생했습니다',
                ];
            }
            // 결과 처리
            $result = json_decode($output[0], true);
            if ($result === false || $result === 'false') {
                return [
                    'status' => true,
                    'return' => $productHref,
                ];
            }
            return [
                'status' => true,
                'return' => $result,
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage() . ' ' . $productHref
            ];
        }
    }
    public function getFileNames($product)
    {
        // 상품 이미지 파일 이름 추출
        $productImage = $product->productImage;
        $productImageFileName = basename($productImage);

        // 상품 상세 내 이미지 파일 이름 추출
        $productDetail = $product->productDetail;
        $productDetailFileNames = $this->extractImageFilenames($productDetail);


        // 상품 이미지 파일 존재 여부 검사
        $productImageDir = public_path('images/CDN/product/' . $productImageFileName);
        if (!is_file($productImageDir)) {
            // 파일이 존재하지 않을 경우
            return [
                'status' => false,
                'return' => [
                    'productImage' => $productImageFileName,
                    'productDetailImages' => $productDetailFileNames
                ]
            ];
        }

        // 모든 검사가 성공적으로 완료될 경우
        return [
            'status' => true,
        ];
    }
    public function getProducts($sellerID)
    {
        $products = DB::table('minewing_products')
            ->where('sellerID', $sellerID)
            ->select('productImage', 'productDetail', 'productHref')
            ->get();
        return $products;
    }
    public function getFileNameFromURL($uRL)
    {
        $path = parse_url($uRL, PHP_URL_PATH);
        $filename = basename($path);
        return $filename;
    }
    public function extractImageFilenames($html)
    {
        $pattern = '/<img[^>]+src="([^"]+)"/';
        preg_match_all($pattern, $html, $matches);

        $filenames = array();
        foreach ($matches[1] as $index => $url) {
            if ($index !== 0) {
                $filenames[] = basename($url);
            }
        }

        return $filenames;
    }
    public function sibal($imageUrl, $newImageName, $path)
    {
        $newWidth = 1000;
        $newHeight = 1000;
        $savePath = public_path('images/CDN/' . $path . '/'); // 경로 수정
        try {
            if ($path === 'product') {
                $image = Image::make($imageUrl)->resize($newWidth, $newHeight);
            } else {
                $image = Image::make($imageUrl);
            }


            $savePathWithFile = $savePath . $newImageName;

            $image->save($savePathWithFile); // 이미지 저장
            return [
                'status' => true,
            ];
        } catch (Exception $e) {
            error_log("Error processing image: " . $e->getMessage());
            return [
                'status' => false,
                'return' => $e->getMessage()
            ];
        }
    }
}
