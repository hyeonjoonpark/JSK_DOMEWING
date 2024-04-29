<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function build($method, $path, $accessKey, $secretKey, $params = "")
    {
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
        date_default_timezone_set("GMT+0");
        $datetime = date("ymd") . 'T' . date("His") . 'Z';
        $method = 'GET';
        $message = $datetime . $method . $path . $query;
        $algorithm = "HmacSHA256";
        $signature = hash_hmac('sha256', $message, $secretKey);
        $authorization  = "CEA algorithm=" . $algorithm . ", access-key=" . $accessKey . ", signed-date=" . $datetime . ", signature=" . $signature;
        $url = 'https://api-gateway.coupang.com' . $path . '?' . $query;
        $response = Http::withHeaders([
            'Authorization' => $authorization,
            'Content-Type' => $contentType
        ])->get($url);
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
    public function builder($accessKey, $secretKey, $method, $contentType, $path, $data)
    {
        date_default_timezone_set("GMT+0");
        $datetime = date("ymd") . 'T' . date("His") . 'Z';
        $message = $datetime . $method . $path;
        $algorithm = "HmacSHA256";
        $signature = hash_hmac('sha256', $message, $secretKey);
        $authorization  = "CEA algorithm=" . $algorithm . ", access-key=" . $accessKey . ", signed-date=" . $datetime . ", signature=" . $signature;
        $url = 'https://api-gateway.coupang.com' . $path;
        $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $response = Http::withHeaders([
            'Authorization' => $authorization,
            'Content-Type' => $contentType
        ])->withBody($jsonData, 'application/json')->$method($url);
        if ($response->successful() && $response->status() === 200) {
            return [
                'status' => true,
                'data' => $response->json()
            ];
        } else {
            return [
                'status' => false,
                'error' => $response->json()
            ];
        }
    }
}
