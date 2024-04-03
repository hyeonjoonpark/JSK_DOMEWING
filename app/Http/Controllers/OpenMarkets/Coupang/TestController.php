<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TestController extends Controller
{
    public function test()
    {
        date_default_timezone_set("GMT+0");

        $datetime = date("ymd") . 'T' . date("His") . 'Z';
        $method = "POST";
        $path = "/v2/providers/openapi/apis/api/v1/categorization/predict";

        $message = $datetime . $method . $path;

        //replace with your own accessKey
        $ACCESS_KEY = "ba20bf45-8ba8-4a41-a050-1d158a55bfe9";
        //replace with your own secretKey
        $SECRET_KEY = "b9172e81915236896964a5c4e5206688f23023f0";
        $vendorId = "A00921418";
        $algorithm = "HmacSHA256";

        $signature = hash_hmac('sha256', $message, $SECRET_KEY);

        $authorization  = "CEA algorithm=HmacSHA256, access-key=" . $ACCESS_KEY . ", signed-date=" . $datetime . ", signature=" . $signature;

        $url = 'https://api-gateway.coupang.com' . $path;
        $strjson = '
        {
            "productName": "패션의류 남성언더웨어 잠옷 시즌성내의 모시메리",
            "vendorId": "A00921418"
        }
        ';

        print nl2br($strjson);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type:  application/json;charset=UTF-8", "Authorization:" . $authorization));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $strjson);
        $result = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        echo ($httpcode);

        echo ($result);
    }
}
