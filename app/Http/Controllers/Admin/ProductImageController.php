<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

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
        $tempImage = $this->downloadImage($imageUrl);

        try {
            $image = Image::make($tempImage)->resize($newWidth, $newHeight);

            $path = parse_url($imageUrl, PHP_URL_PATH);
            $imageExtension = pathinfo($path, PATHINFO_EXTENSION);
            $newImageName = uniqid() . '.' . $imageExtension;
            $savePathWithFile = $savePath . $newImageName;

            $image->save($savePathWithFile); // 이미지 저장

            unlink($tempImage); // 임시 파일 삭제

            return "https://www.sellwing.kr/images/product/" . $newImageName;
        } catch (\Exception $e) {
            error_log("Error processing image: " . $e->getMessage());
            return false;
        }
    }
}