<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
        $savePath = public_path('/images/product/');
        $tempImage = $this->downloadImage($imageUrl);

        list($originalWidth, $originalHeight) = getimagesize($tempImage);
        $path = parse_url($imageUrl, PHP_URL_PATH);

        // pathinfo() 함수를 사용하여 파일 확장자를 추출
        $imageExtension = pathinfo($path, PATHINFO_EXTENSION);

        // Debug: Check the detected image extension
        error_log("Image extension: " . $imageExtension);

        switch ($imageExtension) {
            case 'jpg':
            case 'jpeg':
                $sourceImage = imagecreatefromjpeg($tempImage);
                break;
            case 'png':
                $sourceImage = imagecreatefrompng($tempImage);
                break;
            case 'gif':
                $sourceImage = imagecreatefromgif($tempImage);
                break;
            default:
                echo "Unsupported image format: " . $imageExtension;
                return false;
        }

        $destinationImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($destinationImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        imagedestroy($sourceImage);


        $newImageName = uniqid() . '.' . $imageExtension;
        $savePathWithFile = $savePath . $newImageName;

        switch ($imageExtension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($destinationImage, $savePathWithFile);
                break;
            case 'png':
                imagepng($destinationImage, $savePathWithFile);
                break;
            case 'gif':
                imagegif($destinationImage, $savePathWithFile);
                break;
        }

        imagedestroy($destinationImage);
        unlink($tempImage);
        return "https://www.sellwing.kr/images/product/" . $newImageName;
    }
}