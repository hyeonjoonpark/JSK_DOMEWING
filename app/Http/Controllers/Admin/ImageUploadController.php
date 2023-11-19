<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImageUploadController extends Controller
{
    public function handle(Request $request)
    {
        $image = $request->file;
        // 사용자 정보 가져오기
        $userId = Auth::user()->id;

        // 이미지 파일 이름 생성
        $path = public_path('assets/images/product/desc/');
        $ext = $image->getClientOriginalExtension();
        $imageName = $userId . "_" . date('YmdHis') . "." . $ext;
        $image->move($path, $imageName);

        // 이미지 데이터 가져오기
        $imageData = file_get_contents($path . $imageName);
        $response = $this->uploadToImgur($imageData);
        return $response;
    }
    function uploadToImgur($imageData)
    {
        $clientID = '52d53ac9b9f957b';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.imgur.com/3/image');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                'Authorization: Client-ID ' . $clientID
            )
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt(
            $curl,
            CURLOPT_POSTFIELDS,
            array(
                'image' => base64_encode($imageData),
            )
        );
        // 응답 받기
        set_time_limit(0);
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        // 응답 처리
        if ($response['success'] === true && isset($response['data']['link'])) {
            $imageLink = $response['data']['link'];
            if ($imageLink == 0 || $imageLink == '') {
                $this->uploadToImgur($imageData);
            }
            return $this->getResponseData(1, $imageLink);
        } else {
            return $this->getResponseData(-1, $response['success']);
        }
    }
    function downloadImage($url)
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($tempPath, file_get_contents($url));
        return $tempPath;
    }
    function resizeImage($imageFullPath, $newWidth, $newHeight)
    {
        $tempImage = $this->downloadImage($imageFullPath);
        $imageExtension = pathinfo($tempImage, PATHINFO_EXTENSION);

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
                return false;
        }

        $destinationImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($destinationImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, imagesx($sourceImage), imagesy($sourceImage));
        imagedestroy($sourceImage);

        ob_start();
        switch ($imageExtension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($destinationImage);
                break;
            case 'png':
                imagepng($destinationImage);
                break;
            case 'gif':
                imagegif($destinationImage);
                break;
        }
        $imageData = ob_get_clean();
        imagedestroy($destinationImage);
        unlink($tempImage);

        return $imageData;
    }
    // 응답 데이터 생성
    protected function getResponseData($status, $return)
    {
        return ['status' => $status, 'return' => $return];
    }
}
