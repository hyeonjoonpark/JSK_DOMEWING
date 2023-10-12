<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImageUploadController extends Controller
{
    public function handle(Request $request)
    {
        $clientID = '52d53ac9b9f957b';
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
        $response = $this->uploadToImgur($imageData, $clientID);
        return $response;
    }
    function uploadToImgur($imageData, $clientID)
    {
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
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        // 응답 처리
        if ($response['success'] === true && isset($response['data']['link'])) {
            $imageLink = $response['data']['link'];
            return $this->getResponseData(1, $imageLink);
        } else {
            return $this->getResponseData(-1, $response['success']);
        }
    }
    // 응답 데이터 생성
    protected function getResponseData($status, $return)
    {
        return ['status' => $status, 'return' => $return];
    }
}
