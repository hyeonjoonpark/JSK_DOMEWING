<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Exception;
use DOMDocument;
use DOMXPath;

class ProductImageController extends Controller
{
    public function downloadImage($url)
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($tempPath, file_get_contents($url));
        return $tempPath;
    }

    function index($imageUrl)
    {
        $newWidth = 1000;
        $newHeight = 1000;
        $savePath = public_path('images/product/'); // 경로 수정
        try {
            $image = Image::make($imageUrl)->resize($newWidth, $newHeight);

            $path = parse_url($imageUrl, PHP_URL_PATH);
            $imageExtension = pathinfo($path, PATHINFO_EXTENSION);
            $newImageName = uniqid() . '.' . $imageExtension;
            $savePathWithFile = $savePath . $newImageName;

            $image->save($savePathWithFile); // 이미지 저장
            return "https://www.sellwing.kr/images/product/" . $newImageName;
        } catch (Exception $e) {
            error_log("Error processing image: " . $e->getMessage());
            return false;
        }
    }
    public function preprocessProductDetail($product)
    {
        try {
            $newProductDetail = $this->processProductDetail($product->productDetail);
            return [
                'status' => true,
                'return' => $newProductDetail
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage()
            ];
        }
    }
    private function processProductDetail($productDetail)
    {
        $doc = $this->loadHtmlDocument($productDetail);
        $images = $this->extractImages($doc);

        return $this->createImageHtml($images);
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

    private function createImageHtml($images)
    {
        $html = '<center>';
        foreach ($images as $img) {
            $html .= $this->getImageHtml($img);
        }
        $html .= '</center>';

        return $html;
    }

    private function getImageHtml($img)
    {
        $src = $img->getAttribute('src');
        $imageContent = file_get_contents($src);
        $imageExtension = pathinfo($src, PATHINFO_EXTENSION);
        $imageName = uniqid() . '.' . $imageExtension;
        $savePath = public_path('images/product/detail') . '/' . $imageName;
        file_put_contents($savePath, $imageContent);
        $tmpSrc = "https://www.sellwing.kr/images/product/detail/" . $imageName;

        return '<img src="' . $tmpSrc . '" alt="">';
    }
}