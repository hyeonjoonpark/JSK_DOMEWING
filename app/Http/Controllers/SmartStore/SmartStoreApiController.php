<?php

namespace App\Http\Controllers\SmartStore;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
}
