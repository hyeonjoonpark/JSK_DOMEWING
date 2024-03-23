<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

    function index($imageUrl, $hasWatermark)
    {
        $newWidth = 1000;
        $newHeight = 1000;
        $savePath = public_path('images/CDN/product/'); // 경로 수정
        try {
            $image = Image::make($imageUrl)->resize($newWidth, $newHeight);

            $path = parse_url($imageUrl, PHP_URL_PATH);
            $imageExtension = pathinfo($path, PATHINFO_EXTENSION);
            $newImageName = uniqid() . '.' . $imageExtension;
            $savePathWithFile = $savePath . $newImageName;

            $image->save($savePathWithFile); // 이미지 저장
            $originalImagePath = $savePathWithFile;
            $watermarkImagePath = public_path('images/CDN/jsk_watermark.png');
            if ($hasWatermark == 'Y') {
                $this->applyWatermark($originalImagePath, $watermarkImagePath);
            }
            return [
                'status' => true,
                'return' => "https://www.sellwing.kr/images/CDN/product/" . $newImageName
            ];
        } catch (Exception $e) {
            error_log("Error processing image: " . $e->getMessage());
            return [
                'status' => false,
                'return' => $e->getMessage()
            ];
        }
    }
    function applyWatermark($originalImagePath, $watermarkImagePath)
    {
        // 워터마크 이미지 불러오기
        $watermarkImage = imagecreatefrompng($watermarkImagePath);

        // 워터마크 이미지 크기 조정
        $watermarkWidth = 300; // 워터마크 너비를 300px로 설정
        $watermarkHeight = imagesy($watermarkImage) * ($watermarkWidth / imagesx($watermarkImage)); // 비율 유지
        $watermarkResized = imagecreatetruecolor($watermarkWidth, $watermarkHeight);
        imagealphablending($watermarkResized, false);
        imagesavealpha($watermarkResized, true);
        imagecopyresampled($watermarkResized, $watermarkImage, 0, 0, 0, 0, $watermarkWidth, $watermarkHeight, imagesx($watermarkImage), imagesy($watermarkImage));
        imagedestroy($watermarkImage);
        $watermarkImage = $watermarkResized;

        // 원본 이미지 형식에 따라 이미지 생성
        $imageType = exif_imagetype($originalImagePath);
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $originalImage = imagecreatefromjpeg($originalImagePath);
                break;
            case IMAGETYPE_PNG:
                $originalImage = imagecreatefrompng($originalImagePath);
                break;
            case IMAGETYPE_GIF:
                $originalImage = imagecreatefromgif($originalImagePath);
                break;
            default:
                echo "Unsupported image type";
                return;
        }

        // 원본 이미지에 워터마크 적용
        $x = imagesx($originalImage) - $watermarkWidth;
        $y = imagesy($originalImage) - $watermarkHeight;
        imagecopy($originalImage, $watermarkImage, $x, $y, 0, 0, $watermarkWidth, $watermarkHeight);

        // 결과 이미지 저장
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($originalImage, $originalImagePath);
                break;
            case IMAGETYPE_PNG:
                imagepng($originalImage, $originalImagePath);
                break;
            case IMAGETYPE_GIF:
                imagegif($originalImage, $originalImagePath);
                break;
        }

        imagedestroy($originalImage);
        imagedestroy($watermarkImage);
    }
    function extractImageSrcFromHtml($htmlContent)
    {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true); // HTML이 잘못 형식화된 경우의 오류를 무시
        $doc->loadHTML($htmlContent);
        libxml_clear_errors(); // 오류 버퍼 클리어

        $xpath = new DOMXPath($doc);
        $images = $xpath->query("//img");

        $imageSrcs = [];
        foreach ($images as $img) {
            // 각 이미지 요소의 src 속성을 추출하여 배열에 저장
            $imageSrcs[] = $img->getAttribute('src');
        }
        $html = $this->createImageHtml($imageSrcs);
        return $html;
    }
    public function preprocessProductDetail($productDetail)
    {
        try {
            $newProductDetail = $this->processProductDetail($productDetail);
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
    public function hostImages($imageUrls)
    {
        $hostedImages = array_map(fn ($url) => $this->saveImageAndGetNewUrl($url), $imageUrls);
        // 에러가 발생한 이미지를 필터링하여 제거
        return array_filter($hostedImages, fn ($url) => $url !== null);
    }
    public function saveImageAndGetNewUrl($url)
    {
        try {
            // URL 인코딩
            $img_link = iconv('utf-8', 'euc-kr', $url);
            // 확장자 추출
            $ext = strtolower(pathinfo($img_link, PATHINFO_EXTENSION));
            // 저장할 이미지명 생성
            $img = uniqid() . '.' . $ext;
            $savePath = public_path('images/CDN/detail/' . $img); // 저장 위치 및 파일명

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $img_link);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $contents = curl_exec($ch);
            if ($contents === false) {
                throw new Exception('cURL error: ' . curl_error($ch));
            }
            curl_close($ch);

            // 파일 저장
            if (file_put_contents($savePath, $contents) === false) {
                throw new Exception("Failed to save the image.");
            }

            // 새 이미지 URL 반환
            return 'https://www.sellwing.kr/images/CDN/detail/' . $img;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    protected function encodeUrl($url)
    {
        // Only encode the path part of the URL
        $parsedUrl = parse_url($url);
        $path = implode("/", array_map("rawurlencode", explode("/", $parsedUrl['path'])));
        $encodedUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $path;
        return $encodedUrl;
    }
    // 파일 확장자 추출
    private function getFileExtension($url)
    {
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        return $extension ?: 'jpg'; // 기본값 jpg
    }

    // 새로운 이미지 URL을 이용해 HTML 생성
    public function createHtmlWithImages($images, $headerImage = 'ladam_header.jpg')
    {
        $html = '<center><img src="https://www.sellwing.kr/images/CDN/' . $headerImage . '">';
        foreach ($images as $img) {
            $html .= '<br><img src="' . $img . '" alt="">';
        }
        $html .= '</center>';
        return $html;
    }

    // 이미지 URL 배열을 처리하고 HTML 생성
    public function processImages($imageUrls, $headerImage)
    {
        $hostedImages = $this->hostImages($imageUrls);
        return $this->createHtmlWithImages($hostedImages, $headerImage);
    }
}
