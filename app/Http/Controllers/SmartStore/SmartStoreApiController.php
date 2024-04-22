<?php

namespace App\Http\Controllers\SmartStore;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class SmartStoreApiController extends Controller
{
    public function build($method, $path, $params, $token = "")
    {
        $curl = curl_init();
        $url = "https://api.commerce.naver.com/external" . $path;
        $postFields = http_build_query($params);
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $token",
                "content-type: application/x-www-form-urlencoded"
            ],
            CURLOPT_POSTFIELDS => $postFields
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE); // Check HTTP status code
        curl_close($curl);

        if ($err || $httpCode !== 200) {
            return [
                'status' => false,
                'error' => $err,
                'data' => $response
            ];
        }
        return [
            'status' => true,
            'data' => $response
        ];
    }
    public function builder($account, $contentType, $method, $url, $data)
    {
        $ssac = new SmartStoreAccountController();
        $getAccessTokenResult = $ssac->getAccessToken($account->application_id, $account->secret, $account->username);
        if (!$getAccessTokenResult['status']) {
            return [
                'status' => false,
                'message' => '유효한 API 계정 정보가 아닙니다.',
                'error' => $getAccessTokenResult['message']
            ];
        }
        $accessToken = $getAccessTokenResult['data']->access_token;
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => $contentType
        ])->$method($url, $data);
        if ($response->successful() && $response->status() === 200) {
            return [
                'status' => true,
                'data' => $response->json()
            ];
        } else {
            return [
                'status' => false,
                'error' => $response->body()
            ];
        }
    }
}
