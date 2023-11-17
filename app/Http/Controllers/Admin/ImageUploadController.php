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
            if ($imageLink == 0) {
                $this->uploadToImgur($imageData);
            }
            return $this->getResponseData(1, $imageLink);
        } else {
            return $this->getResponseData(-1, $response['success']);
        }
    }
    function resizeImage($imagePath, $newWidth, $newHeight)
    {
        // 이미지 확장자 가져오기
        $imageExtension = pathinfo($imagePath, PATHINFO_EXTENSION);

        // 원본 이미지를 불러옵니다.
        switch ($imageExtension) {
            case 'jpg':
            case 'jpeg':
                $sourceImage = imagecreatefromjpeg($imagePath);
                break;
            case 'png':
                $sourceImage = imagecreatefrompng($imagePath);
                break;
            case 'gif':
                $sourceImage = imagecreatefromgif($imagePath);
                break;
            // 다른 이미지 확장자를 처리할 수 있도록 필요한 경우 추가합니다.
            default:
                // 지원하지 않는 이미지 형식인 경우 처리
                return null;
        }

        // 새로운 크기로 이미지를 조정합니다.
        $destinationImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($destinationImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, imagesx($sourceImage), imagesy($sourceImage));

        // 메모리에서 이미지를 제거합니다.
        imagedestroy($sourceImage);

        // 이미지 데이터를 반환합니다.
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
            // 다른 이미지 확장자를 처리할 수 있도록 필요한 경우 추가합니다.
        }
        $imageData = ob_get_clean();
        imagedestroy($destinationImage);


        $this->saveImageToFile($imageData, public_path('images\product'));
    }
    function saveImageToFile($imageData, $filePath)
    {
        // 파일에 이미지 데이터를 쓴다.
        $result = file_put_contents($filePath, $imageData);

        // 파일 쓰기 성공 여부를 반환한다.
        return $result !== false;
    }
    // 응답 데이터 생성
    protected function getResponseData($status, $return)
    {
        return ['status' => $status, 'return' => $return];
    }
}
