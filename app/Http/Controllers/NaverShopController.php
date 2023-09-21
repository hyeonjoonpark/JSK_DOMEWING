<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class NaverShopController extends Controller
{
    public function getCategories()
    {
        $client_id = '5QuwVHMFmrkfq98AdIgu43';
        $client_secret = '$2a$04$EEaznqQ.Sk3Uz5z6mkZ3Ve';
        $timestamp = (int) (microtime(true) * 1000);
        $password = $client_id . '_' . $timestamp;
        $hashed = crypt($password, $client_secret);
        $b64_hashed = base64_encode($hashed);

        # 1) 인증토큰
        $aGetParm = [
            "client_id" => $client_id,
            "timestamp" => $timestamp,
            "grant_type" => "client_credentials",
            "type" => "SELF",
            "client_secret_sign" => "$b64_hashed"
        ];
        $queryString = http_build_query($aGetParm);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.commerce.naver.com/external/v1/oauth2/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $queryString,
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo $queryString . PHP_EOL;
            echo "cURL Error #:" . $err . PHP_EOL;
            exit;
        } else {
            echo '<pre>' . print_r($response, true) . '</pre>';
            $oJson = json_decode($response);
            $access_token = $oJson->access_token;
            $expires_in = $oJson->expires_in;
            $token_type = $oJson->token_type;
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.commerce.naver.com/external/v1/products/search",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer $access_token",
                    "content-type: application/json"
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                echo $response;
            }
        }
    }
}