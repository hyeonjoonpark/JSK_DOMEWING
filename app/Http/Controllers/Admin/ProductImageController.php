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
        try {
            $image = Image::make($imageUrl)->resize($newWidth, $newHeight);

            $path = parse_url($imageUrl, PHP_URL_PATH);
            $imageExtension = pathinfo($path, PATHINFO_EXTENSION);
            $newImageName = uniqid() . '.' . $imageExtension;
            $savePathWithFile = $savePath . $newImageName;

            $image->save($savePathWithFile); // 이미지 저장
            return "https://www.sellwing.kr/images/product/" . $newImageName;
        } catch (\Exception $e) {
            $this->index($imageUrl);
        }
    }
}