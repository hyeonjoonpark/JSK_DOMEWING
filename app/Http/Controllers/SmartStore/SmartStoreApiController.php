<?php

namespace App\Http\Controllers\SmartStore;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class SmartStoreApiController extends Controller
{
    public function build($method, $path, $params, $token = "")
    {
        ini_set('max_execution_time', 120);
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
    public function putBuilder($account, $contentType, $method, $url, $data)
    {
        ini_set('max_execution_time', 120);
        $ssac = new SmartStoreAccountController();
        $getAccessTokenResult = $ssac->getAccessToken($account->application_id, $account->secret, $account->username);
        if (!$getAccessTokenResult['status']) {
            return [
                'status' => false,
                'message' => 'Invalid API account information.',  // 메시지 개선
                'error' => $getAccessTokenResult['message']
            ];
        }

        $accessToken = $getAccessTokenResult['data']->access_token;
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => $contentType
            ])->{$method}($url, $data);  // 동적 메서드 호출은 유지

            if ($response->successful()) {
                return [
                    'status' => true,
                    'data' => $response->json()
                ];
            } else {
                return [
                    'status' => false,
                    'error' => $response->body(),
                    'status_code' => $response->status()  // 상태 코드 추가
                ];
            }
        } catch (\Exception $e) {  // 예외 처리 추가
            return [
                'status' => false,
                'error' => $e->getMessage()
            ];
        }
    }


    public function builder($account, $contentType, $method, $url, $data)
    {
        ini_set('max_execution_time', 120);
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
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => $contentType
            ])->$method($url, $data);
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '마켓으로부터의 응답 시간이 초과하였습니다.',
                'error' => $e->getMessage()
            ];
        }
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
