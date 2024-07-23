<?php

namespace App\Http\Controllers\OpenMarkets\LotteOn;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class LotteOnApiController extends Controller
{
    public function builder(string $method, string $accessKey, string $url, array $data = null)
    {
        ini_set('max_execution_time', 120);
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessKey,
                'Accept' => "application/json",
                'Accept-Language' => 'ko',
                'X-Timezone' => "GMT+09:00",
                "Content-Type" => "application/json"
            ])->$method($url, $data);
            return [
                'status' => true,
                'data' => $response->json()
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'API 요청에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    public function getBuilder($accessKey, $url)
    {
        // cURL 세션 초기화
        $ch = curl_init();
        // cURL 옵션 설정
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100); // 초 단위
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json', // 필수헤더 application/json
            'Accept-Language: ko', // 필수헤더 국내접속일 경우 "ko"
            'X-Timezone: GMT+09:00', // 필수헤더 국내접속일 경우 "GMT+09:00"
            'Authorization: Bearer ' . $accessKey, // 인증키
            'Cache-Control: no-cache', // 캐시 제어
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        // 응답을 핸들링
        if ($result === false) {
            $error = curl_error($ch);
            return [
                'status' => false,
                'message' => '마켓으로부터의 응답 시간이 초과하였습니다.',
                'error' => $error
            ];
        }
        if ($httpcode === 200) {
            return [
                'status' => true,
                'data' => json_decode($result, true),
                'httpcode' => $httpcode
            ];
        }
        return [
            'status' => false,
            'message' => '마켓으로부터의 응답이 올바르지 않습니다.',
            'error' => $result,
            'httpcode' => $httpcode
        ];
    }
    public function postBuilder($accessKey, $url, $postData)
    {
        $postData = json_encode($postData);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100); // 초 단위
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json', // 필수헤더 application/json
            'Accept-Language: ko', // 필수헤더 국내접속일 경우 "ko"
            'X-Timezone: GMT+09:00', // 필수헤더 국내접속일 경우 "GMT+09:00"
            'Authorization: Bearer ' . $accessKey, // 인증키
            'Cache-Control: no-cache', // 캐시 제어
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
        }
        curl_close($ch);
        $decodedResult = json_decode($result, true);
        return [
            'status' => isset($error_msg) ? false : true,
            'data' => [
                'result' => $decodedResult,
                'httpcode' => $httpcode,
                'error' => isset($error_msg) ? $error_msg : null
            ]
        ];
    }
    public function index() //테스트 api
    {
        // API 주소
        $accessKey = '5d5b2cb498f3d20001665f4ed04a48bf370a4d37a64c6394431f2cef';
        $url = 'https://openapi.lotteon.com/v1/openapi/contract/v1/dvl/getDvCstListSr';
        // POST 데이터 설정 (JSON 형식으로 인코딩)
        $postData = json_encode([
            'afflTrCd' => 'LO10043084'
        ]);
        // cURL 세션 초기화
        $ch = curl_init();
        // cURL 옵션 설정
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100); // 초 단위
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json', // 필수헤더 application/json
            'Accept-Language: ko', // 필수헤더 국내접속일 경우 "ko"
            'X-Timezone: GMT+09:00', // 필수헤더 국내접속일 경우 "GMT+09:00"
            'Authorization: Bearer ' . $accessKey, // 인증키
            'Cache-Control: no-cache', // 캐시 제어
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        // 응답 결과를 변수에 저장
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // 에러 처리
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
        }
        // cURL 세션 닫기
        curl_close($ch);
        // JSON 응답 디코딩
        $decodedResult = json_decode($result, true);
        // 응답 및 에러 처리 결과 반환
        return [
            'status' => isset($error_msg) ? false : true,
            'data' => [
                'result' => $decodedResult,
                'httpcode' => $httpcode,
                'error' => isset($error_msg) ? $error_msg : null
            ]
        ];
    }
}
