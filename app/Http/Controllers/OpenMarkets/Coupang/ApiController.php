<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function build($method, $path, $accessKey, $secretKey, $params = "")
    {
        ini_set('max_execution_time', 120);
        date_default_timezone_set("GMT+0");
        $datetime = date("ymd") . 'T' . date("His") . 'Z';
        $message = $datetime . $method . $path;
        $algorithm = "HmacSHA256";
        $signature = hash_hmac('sha256', $message, $secretKey);
        $authorization  = "CEA algorithm=" . $algorithm . ", access-key=" . $accessKey . ", signed-date=" . $datetime . ", signature=" . $signature;
        $url = 'https://api-gateway.coupang.com' . $path;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type:  application/json;charset=UTF-8", "Authorization:" . $authorization));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        $result = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return [
            'status' => true,
            'data' => [
                'result' => $result,
                'httpcode' => $httpcode
            ]
        ];
    }
    public function getBuilder(string $accessKey, string $secretKey, string $contentType, string $path, string $query = '')
    {
        ini_set('max_execution_time', 120);
        date_default_timezone_set("GMT+0");
        $datetime = date("ymd") . 'T' . date("His") . 'Z';
        $method = 'GET';
        $message = $datetime . $method . $path . $query;
        $algorithm = "HmacSHA256";
        $signature = hash_hmac('sha256', $message, $secretKey);
        $authorization  = "CEA algorithm=" . $algorithm . ", access-key=" . $accessKey . ", signed-date=" . $datetime . ", signature=" . $signature;
        $url = 'https://api-gateway.coupang.com' . $path . '?' . $query;
        try {
            $response = Http::withHeaders([
                'Authorization' => $authorization,
                'Content-Type' => $contentType
            ])->get($url);
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
                'message' => '쿠팡윙에서 반품지 주소를 올바르게 설정해주세요.<br>혹은 쿠팡 API 에 43.200.252.11 IP 주소를 기입해주세요.',
                'error' => $response->body()
            ];
        }
    }
    public function deleteBuilder(string $accessKey, string $secretKey, string $contentType, string $path, string $query = '')
    {
        ini_set('max_execution_time', 120);
        date_default_timezone_set("GMT+0");
        $datetime = date("ymd") . 'T' . date("His") . 'Z';
        $method = "DELETE";
        $message = $datetime . $method . $path . $query;
        $algorithm = "HmacSHA256";
        $signature = hash_hmac('sha256', $message, $secretKey);
        $authorization  = "CEA algorithm=" . $algorithm . ", access-key=" . $accessKey . ", signed-date=" . $datetime . ", signature=" . $signature;
        $url = 'https://api-gateway.coupang.com' . $path . '?' . $query;
        try {
            $response = Http::withHeaders([
                'Authorization' => $authorization,
                'Content-Type' => $contentType
            ])->delete($url);
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
    public function putBuilder(string $accessKey, string $secretKey, string $contentType, string $path, array $data = [])
    {
        ini_set('max_execution_time', 120);
        date_default_timezone_set("GMT+0");
        $datetime = date("ymd") . 'T' . date("His") . 'Z';
        $method = "PUT";
        $message = $datetime . $method . $path;
        $algorithm = "HmacSHA256";
        $signature = hash_hmac('sha256', $message, $secretKey);
        $authorization  = "CEA algorithm=" . $algorithm . ", access-key=" . $accessKey . ", signed-date=" . $datetime . ", signature=" . $signature;
        $url = 'https://api-gateway.coupang.com' . $path;
        $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        try {
            $response = Http::withHeaders([
                'Authorization' => $authorization,
                'Content-Type' => $contentType
            ])->withBody($jsonData, 'application/json')->put($url);
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
                'message' => '쿠팡윙에서 반품지 주소를 올바르게 설정해주세요.<br>혹은 쿠팡 API 에 43.200.252.11 IP 주소를 기입해주세요.',
                'error' => $response->body()
            ];
        }
    }
    public function builder($accessKey, $secretKey, $method, $contentType, $path, $data)
    {
        ini_set('max_execution_time', 120);
        date_default_timezone_set("GMT+0");
        $datetime = gmdate("ymd") . 'T' . gmdate("His") . 'Z';
        $message = $datetime . $method . $path;
        $algorithm = "HmacSHA256";
        $signature = hash_hmac('sha256', $message, $secretKey);
        $authorization = "CEA algorithm=" . $algorithm . ", access-key=" . $accessKey . ", signed-date=" . $datetime . ", signature=" . $signature;
        $url = 'https://api-gateway.coupang.com' . $path;
        $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        try {
            $response = Http::withHeaders([
                'Authorization' => $authorization,
                'Content-Type' => $contentType,
            ])
                ->withBody($jsonData, 'application/json')
                ->{$method}($url);
            if ($response->successful() && $response->status() === 200) {
                return [
                    'status' => true,
                    'data' => $response->json(),
                ];
            } else {
                return [
                    'status' => false,
                    'error' => $response->json(),
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '마켓으로부터의 응답 시간이 초과하였습니다.',
                'error' => $e->getMessage(),
            ];
        }
    }
}
