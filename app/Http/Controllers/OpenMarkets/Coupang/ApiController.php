<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
}
