<?php

namespace App\Http\Controllers\Recover;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Exception;
use DOMDocument;
use DOMXPath;

class IndexController extends Controller
{
    public function index()
    {
        $products = $this->getProducts();
        foreach ($products as $product) {
            $newImageFileName = $this->getFileNameFromURL($product->newImageHref);
            $this->sibal($product->productImage, $newImageFileName);
            $newDetailFileNames = $this->extractImageFilenames($product->newProductDetail);
            $this->processProductDetail($product->productDetail, $newDetailFileNames);
        }
        return true;
    }
    public function getProducts()
    {
        $products = DB::table('uploaded_products AS up')
            ->join('collected_products AS cp', 'up.productId', '=', 'cp.id')
            ->join('vendors AS v', 'cp.sellerID', '=', 'v.id')
            ->where('cp.isActive', 'Y')
            ->where('up.isActive', 'Y')
            ->get();
        return $products;
    }
    public function getFileNameFromURL($uRL)
    {
        $path = parse_url($uRL, PHP_URL_PATH);
        $filename = basename($path);
        return $filename;
    }
    function extractImageFilenames($html)
    {
        $pattern = '/<img[^>]+src="([^"]+)"/';
        preg_match_all($pattern, $html, $matches);

        $filenames = array();
        foreach ($matches[1] as $url) {
            $filenames[] = basename($url);
        }

        return $filenames;
    }
    function sibal($imageUrl, $newImageName)
    {
        $newWidth = 1000;
        $newHeight = 1000;
        $savePath = public_path('images/product/'); // 경로 수정
        try {
            $image = Image::make($imageUrl)->resize($newWidth, $newHeight);

            $savePathWithFile = $savePath . $newImageName;

            $image->save($savePathWithFile); // 이미지 저장
            return [
                'status' => true,
                'return' => "https://www.sellwing.kr/images/product/" . $newImageName
            ];
        } catch (Exception $e) {
            error_log("Error processing image: " . $e->getMessage());
            return [
                'status' => false,
                'return' => $e->getMessage()
            ];
        }
    }
    private function processProductDetail($productDetail, $newProductDetailNames)
    {
        $doc = $this->loadHtmlDocument($productDetail);
        $images = $this->extractImages($doc);

        return $this->createImageHtml($images, $newProductDetailNames);
    }

    private function loadHtmlDocument($htmlContent)
    {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($htmlContent);
        libxml_clear_errors();

        return $doc;
    }

    private function extractImages(DOMDocument $doc)
    {
        $xpath = new DOMXPath($doc);
        return $xpath->query("//img");
    }

    private function createImageHtml($images, $newDetailFileNames)
    {
        $html = '<center>';
        for ($i = 0; $i < count($images); $i++) {
            $html .= $this->getImageHtml($images[$i], $newDetailFileNames[$i]);
        }
        $html .= '</center>';

        return $html;
    }

    private function getImageHtml($img, $newProductDetailName)
    {
        $src = $img->getAttribute('src');
        $imageContent = file_get_contents($src);
        $savePath = public_path('images/product/detail') . '/' . $newProductDetailName;
        file_put_contents($savePath, $imageContent);
        $tmpSrc = "https://www.sellwing.kr/images/product/detail/" . $newProductDetailName;

        return '<img src="' . $tmpSrc . '" alt="">';
    }
}
