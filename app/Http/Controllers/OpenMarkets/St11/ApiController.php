<?php

namespace App\Http\Controllers\OpenMarkets\St11;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class ApiController extends Controller
{
    public function builder($apiKey, $method, $url, $data = '')
    {
        $response = Http::withHeaders([
            'Content-type' => 'text/xml;charset=EUC-KR',
            'openapikey' => $apiKey,
        ])->withBody(iconv('UTF-8', 'EUC-KR', $data), 'text/xml')
            ->$method($url);
        if ($response->successful()) {
            $xml = simplexml_load_string($response->body());
            return [
                'status' => true,
                'data' => $xml
            ];
        }
        return [
            'status' => false,
            'message' => '11번가 API 요청을 보내는 과정에서 에러가 발생했습니다.',
            'error' => mb_convert_encoding($response->body(), 'UTF-8', 'EUC-KR')
        ];
    }
    public function orderBuilder($apiKey, $method, $url, $data = '')
    {
        $response = Http::withHeaders([
            'Content-type' => 'text/xml;charset=EUC-KR',
            'openapikey' => $apiKey,
        ])->withBody(iconv('UTF-8', 'EUC-KR', $data), 'text/xml')
            ->$method($url);
        if ($response->successful()) {
            $euc_kr_body = $response->body();
            Log::info('Original Response Body (EUC-KR): ' . $euc_kr_body);
            // XML 인코딩 선언 변경
            $euc_kr_body = preg_replace('/<\?xml version="1.0" encoding="EUC-KR"\?>/', '<?xml version="1.0" encoding="UTF-8"?>', $euc_kr_body);
            // mb_convert_encoding을 사용하여 변환
            $utf8_response_body = mb_convert_encoding($euc_kr_body, 'UTF-8', 'EUC-KR');
            Log::info('Converted UTF-8 Response Body: ' . $utf8_response_body);
            if (mb_detect_encoding($utf8_response_body, 'UTF-8', true) === false) {
                return [
                    'status' => false,
                    'message' => 'UTF-8 변환 실패',
                    'error' => '응답 본문을 UTF-8로 변환하는 과정에서 에러가 발생했습니다.'
                ];
            }
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($utf8_response_body);
            if ($xml === false) {
                $errors = libxml_get_errors();
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->message;
                }
                libxml_clear_errors();
                return [
                    'status' => false,
                    'message' => 'XML 파싱 실패',
                    'error' => 'XML을 파싱하는 과정에서 에러가 발생했습니다: ' . implode(", ", $errorMessages),
                    'response_body' => $utf8_response_body
                ];
            }
            $xmlArray = json_decode(json_encode($xml), true);
            $jsonFormattedData = json_encode($xmlArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            Log::info('Formatted Response Data: ' . $jsonFormattedData);
            return [
                'status' => true,
                'data' => $xmlArray
            ];
        }
        return [
            'status' => false,
            'message' => 'API 요청을 보내는 과정에서 에러가 발생했습니다.',
            'error' => mb_convert_encoding($response->body(), 'UTF-8', 'EUC-KR')
        ];
    }
}
